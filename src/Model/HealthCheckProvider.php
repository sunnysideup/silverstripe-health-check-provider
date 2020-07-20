<?php

namespace Sunnysideup\HealthCheckProvider\Model;

use SilverStripe\Control\Director;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
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

    private const NOT_PROVIDED_PHRASE = 'not provided';

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
        'Retrieved' => 'Boolean',
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

    private static $field_labels = [
        'SendNow' => 'Send now?',
        'Sent' => 'Has been sent',
        'Retrieved' => 'Has been retrieved',
        'HealthCheckItemProviders' => 'Pieces of Info',
    ];

    private static $summary_fields = [
        'Title' => 'Health Report Data',
        'Sent.Nice' => 'Sent',
        'Retrieved.Nice' => 'Retrieved',
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
            $this->Data = json_encode($this->retrieveDataInner());
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
        } else {
            //only triggers when ready!
            $this->send();
        }
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
                'Retrieved',
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
                            'Data Points to be Provided',
                            HealthCheckItemProvider::get()->filter(['Include' => true])->map('ID', 'CodeNice')
                        )->setDescription('Please untick any data that you prefer not to provide.'),
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
                    ReadonlyField::create('HasBeenRetried', 'Retrieved', $this->dbObject('Retrieved')->Nice()),
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
                        '<h2>Data</h2><pre>' . json_encode(json_decode($this->Data), JSON_PRETTY_PRINT) . '</pre>'
                    ),
                ]
            );
            $fields->addFieldsToTab(
                'Root.PiecesOfInfo',
                [
                    GridField::create(
                        'HealthCheckItemProvidersList',
                        'Data List',
                        HealthCheckItemProvider::get(),
                        GridFieldConfig_RecordEditor::create()
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

    public function send()
    {
        if ($this->SendNow && ! $this->Sent) {
            //create final data
            $this->SendNow = false;
            $this->write();

            // mark as sent
            $this->Sent = true;
            $this->SendCode = hash('ripemd160', $this->Data);
            $this->write();

            if ($this->Retrieved) {
                //todo: make more secure
                $this->ReceiptCode = $this->SendCode;
            } else {
                //send data
                $sender = new SendData();
                $sender->setData($this->Data);
                //confirm outcome
                $this->ReceiptCode = $sender->send();
            }
            $this->write();
        }
    }

    public function retrieve()
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
        $base = Director::host();

        return rtrim($base, '/');
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
        $includeIDList = $this->HealthCheckItemProviders()
            ->filter(['Include' => true])
            ->column('ID');
        $list = HealthCheckItemProvider::get();
        foreach ($list as $item) {
            $shortName = $item->getCode();
            if (in_array($item->ID, $includeIDList, false)) {
                $rawData['Data'][$shortName] = $item->findAnswer($this);
            } else {
                $rawData['Data'][$shortName] = SELF::NOT_PROVIDED_PHRASE;
            }
        }
        return $rawData;
    }
}
