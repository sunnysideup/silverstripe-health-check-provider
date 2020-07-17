<?php

namespace Sunnysideup\HealthCheckProvider\Model;

use SilverStripe\Control\Director;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\HTMLReadonlyField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use Sunnysideup\HealthCheckProvider\Api\SendData;

class HealthCheckProvider extends DataObject
{

    private const VIEW_URL = 'https://check.silverstripe-webdevelopment.com/report/view/';

    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Health Check Data';

    private static $plural_name = 'Health Check Data Lists';

    private static $table_name = 'HealthCheckProvider';

    #######################
    ### Model Section
    #######################

    private static $db = [
        'MainUrl' => 'Varchar(255)',
        'OtherUrls' => 'Text',
        'SendNow' => 'Boolean',
        'Sent' => 'Boolean',
        'SendCode' => 'Varchar',
        'ReceiptCode' => 'Varchar',
        'HasError' => 'Boolean',
        'Data' => 'Text',
    ];

    private static $has_one = [
        'Editor' => Member::class,
    ];

    private static $many_many = [
        'HealthCheckItemProviders' => HealthCheckItemProvider::class,
    ];

    #######################
    ### Further DB Field Details
    #######################

    private static $default_sort = [
        'Created' => 'DESC',
    ];

    #######################
    ### Field Names and Presentation Section
    #######################

    private static $field_labels_right = [
        'SendNow' => 'Send now?',
        'Sent' => 'Has been sent',
    ];

    private static $summary_fields = [
        'Title' => 'Health Report Data',
        'Sent.Nice' => 'Sent',
        'HasError.Nice' => 'Error',
        'Editor.Title' => 'Editor',
    ];

    #######################
    ### Casting Section
    #######################

    private static $casting = [
        'Title' => 'Varchar',
    ];

    /**
     * casted variable
     * @return string
     */
    public function getTitle(): string
    {
        $str = 'Health Check for ' . $this->MainUrl;
        if ($this->Sent) {
            $str .= '; Sent on ' . $this->dbObject('LastEdited')->Nice();
        } else {
            $str .= '; Not sent yet';
        }
        return $str;
    }

    #######################
    ### can Section
    #######################

    public function canDelete($member = null)
    {
        if ($this->Sent) {
            return false;
        }
        return parent::canDelete($member);
    }

    public function canEdit($member = null)
    {
        if ($this->Sent) {
            return false;
        }
        return parent::canEdit($member);
    }

    #######################
    ### write Section
    #######################

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (! $this->EditorID) {
            $this->EditorID = Security::getCurrentUser()->ID;
        }
        if (! $this->MainUrl) {
            $this->MainUrl = $this->getSiteURL();
        }
        if (! $this->Sent) {
        }
        if ($this->Sent) {
            $this->HasError = $this->SendCode === $this->ReceiptCode ? false : true;
        } else {
            $this->Data = json_encode($this->retrieveDataInner(), JSON_PRETTY_PRINT);
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if (intval($this->HealthCheckItemProviders()->count()) === 0) {
            foreach (HealthCheckItemProvider::get()->filter(['Include' => true]) as $item) {
                $this->HealthCheckItemProviders()->add($item);
            }
            register_shutdown_function([$this, 'write']);
        }

        //only triggers when ready!
        $this->send();
    }

    #######################
    ### CMS Edit Section
    #######################

    public function populateDefaults()
    {
        parent::populateDefaults();
        $this->MainUrl = $this->getSiteURL();
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(
            [
                'SendCode',
                'ReceiptCode',
                'Sent',
                'Data',
                'EditorID',
            ]
        );
        if ($this->exists()) {
            $fields->removeByName(
                [
                    'HealthCheckItemProviders',
                ]
            );
            if ($this->Sent) {
                $fields->removeByName(
                    [
                        'SendNow',
                    ]
                );
                $viewLink = $this->ViewLink();
                if ($viewLink) {
                    $fields->addFieldsToTab(
                        'Root.Main',
                        [
                            HTMLReadonlyField::create(
                                'Link',
                                'Open report',
                                '<a href="' . $viewLink . '">View Link</a>'
                            ),
                        ]
                    );
                }
            } else {
                $fields->removeByName(
                    [
                        'HasError',
                    ]
                );
                $fields->addFieldsToTab(
                    'Root.Main',
                    [
                        CheckboxSetField::create(
                            'HealthCheckItemProviders',
                            'Data Points',
                            HealthCheckItemProvider::get()->filter(['Include' => true])->map()
                        ),
                    ]
                );
            }
            $fields->addFieldsToTab(
                'Root.Main',
                [
                    TextField::create('MainUrl', 'Main URL'),
                    TextField::create('MainUrl', 'Main URL'),
                    TextField::create('OtherUrls', 'Other Urls')
                        ->setDescription('Separate by comma - e.g. new.mysite.com, otherurl.com, etc ...'),
                    ReadonlyField::create('HasBeenSent', 'Sent', $this->dbObject('Sent')->Nice()),
                    ReadonlyField::create('Created'),
                    ReadonlyField::create('Editor Email', 'Editor Email', $this->Editor()->Email),
                    ReadonlyField::create('LastEdited'),
                ]
            );
            $fields->addFieldsToTab(
                'Root.Output',
                [
                    ReadonlyField::create('SendCode'),
                    ReadonlyField::create('ResponseCode'),
                    LiteralField::create(
                        'Output',
                        '<h2>Data</h2><pre>' . $this->Data . '</pre>'
                    ),
                ]
            );
        } else {
            $fields->removeByName(
                [
                    'SendNow',
                    'HasError',
                ]
            );
        }
        return $fields;
    }

    public function ViewLink()
    {
        if ($this->ResponseCode) {
            return self::VIEW_URL . $this->ResponseCode . '/';
        }
    }

    protected function send()
    {
        if ($this->SendNow && ! $this->Sent) {
            //create final data
            $this->SendNow = false;
            $this->write();

            // mark as sent
            $this->Sent = true;
            $this->SendCode = hash('ripemd160', $this->Data);
            $this->write();

            //send data
            $sender = new SendData();
            $sender->setData($this->Data);

            //confirm outcome
            $this->ReceiptCode = $sender->send();
            $this->write();
        }
    }

    /**
     * Return the host website URL
     * @return string             URL of the host website
     */
    protected function getSiteURL(): string
    {
        $base = Director::absoluteBaseURL();
        $base = str_replace('https://', '', $base);
        $base = rtrim($base, '/');

        return str_replace('http://', '', $base);
    }

    #######################
    ### Calculations
    #######################

    protected function retrieveDataInner(): array
    {
        $rawData = [
            'MainUrl' => $this->MainUrl,
            'OtherUrls' => $this->OtherUrls,
            'Editor' => [
                'Email' => $this->Editor()->Email,
                'FirstName' => $this->Editor()->FirstName,
                'Surname' => $this->Editor()->Surname,
            ],
            'Data' => [],
        ];
        $list = $this->HealthCheckItemProviders()->filter(['Include' => true]);
        foreach ($list as $item) {
            $rawData['Data'][$item->RunnerClassName] = $item->findAnswer($this);
        }
        return $rawData;
    }
}
