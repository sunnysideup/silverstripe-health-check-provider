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

class HealthCheckProvider extends DataObject
{
    private const VIEW_URL = 'https://check.silverstripe-webdevelopment.com/report/view/';

    private const NOT_PROVIDED_PHRASE = 'not provided';

    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Report';

    private static $plural_name = 'Reports';

    private static $table_name = 'HealthCheckProvider';

    #######################
    ### Model Section
    #######################

    private static $db = [
        'MainUrl' => 'Varchar(255)',
        'OtherUrls' => 'Text',
        'SendNow' => 'Boolean',
        'Sent' => 'Boolean',
        'SendCode' => 'Varchar(125)',
        'ResponseCode' => 'Varchar(125)',
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

    private static $indexes = [
        'SendCode' => true,
        'ResponseCode' => true,
    ];

    private static $default_sort = [
        'Created' => 'DESC',
    ];

    #######################
    ### Field Names and Presentation Section
    #######################

    private static $field_labels = [
        'SendNow' => 'Send now?',
        'Sent' => 'Has been sent',
        'HealthCheckItemProviders' => 'Pieces of Info',
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
            $user = Security::getCurrentUser();
            if ($user) {
                $this->EditorID = Security::getCurrentUser()->ID;
            }
        }
        if (! $this->MainUrl) {
            $this->MainUrl = $this->getSiteURL();
        }
        if ($this->Sent) {
            $this->HasError = $this->getCodesMatch() ? false : true;
        } elseif ($this->SendCode) {
            $this->Data = json_encode($this->retrieveDataInner());
        }
        if($this->exists() && $this->hasAnswers()) {
            if(! ($this->Sent || $this->SendCode)) {
                $this->SendCode = $this->createSendCode();
            }
        }
    }

    public function getCodesMatch(): bool
    {
        if ($this->Sent) {
            return $this->SendCode === $this->ResponseCode;
        }
        return true;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if (! $this->hasAnswers()) {
            foreach (HealthCheckItemProvider::get()->filter(['Include' => true]) as $item) {
                $this->HealthCheckItemProviders()->add($item);
            }
            register_shutdown_function([$this, 'write']);
        } elseif(! $this->Data) {
            register_shutdown_function([$this, 'write']);
        } else {
            //only triggers when ready!
            $this->send();
        }
    }

    protected function hasAnswers() : bool
    {
        return (intval($this->HealthCheckItemProviders()->count()) > 0) ? true : false;
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
                'SendNow',
                'ResponseCode',
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

            $this->write();
        }
    }

    protected function createSendCode(): string
    {
        $array = $this->retrieveDataInnerInner();
        $serialized = serialize($array);

        return hash('ripemd160', $this->ID . $serialized);
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
        return [
            'ID' => $this->ID,
            'SendCode' => $this->SendCode,
            'MainUrl' => $this->MainUrl,
            'OtherUrls' => $this->OtherUrls,
            'Editor' => [
                'Email' => $this->Editor()->Email,
                'FirstName' => $this->Editor()->FirstName,
                'Surname' => $this->Editor()->Surname,
            ],
            'Data' => $this->retrieveDataInnerInner(),
        ];
    }

    protected $cacheForData = [];

    protected function retrieveDataInnerInner(): array
    {
        if (count($this->cacheForData) === 0) {
            $this->cacheForData = [];
            $includeIDList = $this->HealthCheckItemProviders()
                ->filter(['Include' => true])
                ->column('ID');
            $list = HealthCheckItemProvider::get();
            foreach ($list as $item) {
                $shortName = $item->getCode();
                if (in_array($item->ID, $includeIDList, false)) {
                    $this->cacheForData[$shortName] = $item->findAnswer($this);
                } else {
                    $this->cacheForData[$shortName] = SELF::NOT_PROVIDED_PHRASE;
                }
            }
        }

        return $this->cacheForData;
    }
}
