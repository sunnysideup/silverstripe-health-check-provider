<?php

namespace Sunnysideup\HealthCheckProvider\Model;

use SilverStripe\Control\Director;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use Sunnysideup\HealthCheckProvider\Api\SendData;

class HealthCheckProvider extends DataObject
{
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
        'Editor.Title' => 'Editor',
    ];

    #######################
    ### Casting Section
    #######################

    private static $casting = [
        'Title' => 'Varchar',
        'HasError' => 'Boolean',
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

    /**
     * casted variable
     * @return string
     */
    public function getHasError(): string
    {
        $val = $this->SendCode === $this->ReceiptCode ? false : true;

        return DBField::create_field('Boolean', $val);
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
            $this->Data = json_encode($this->retrieveDataInner(), JSON_PRETTY_PRINT);
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->SendNow) {
            $this->send();
            $this->SendNow = false;
            $this->write();
        }
    }

    #######################
    ### CMS Edit Section
    #######################

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(
            [
                'SendCode',
                'ReceiptCode',
                'HealthCheckItemProviders',
                'MainUrl',
                'OtherUrls',
                'Data',
            ]
        );
        $fields->addFieldsToTab(
            'Root.Main',
            [
                TextField::create('MainUrl', 'Main URL'),
                TextField::create('OtherUrls', 'Other Urls')
                    ->setDescription('Separate by comma - e.g. new.mysite.com, otherurl.com, etc ...'),
                ReadonlyField::create('Created'),
                ReadonlyField::create('LastEdited'),
                CheckboxSetField::create(
                    'HealthCheckItemProviders',
                    'Data Points',
                    HealthCheckItemProvider::get()->map()
                ),
            ]
        );
        $fields->addFieldsToTab(
            'Root.Output',
            [
                LiteralField::create(
                    'Output',
                    '<pre>' . $this->Data . '</pre>'
                ),
            ]
        );

        return $fields;
    }

    protected function send()
    {
        $this->SendCode = hash('ripemd160', $this->Data);
        $this->ReceiptCode = $this->send();
    }

    protected function curlRequest()
    {
        $sender = new SendData();
        $sender->setData($this->Data);
        $sender->send();
    }

    /**
     * Return the host website URL
     * @return string             URL of the host website
     */
    protected function getSiteURL(): string
    {
        $base = Director::absoluteBaseURL();
        $base = str_replace('https://', '', $base);
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
            'Editor' => $this->Editor()->Email,
            'Data' => [],
        ];
        $list = HealthCheckItemProvider::get()->filter(['Enabled' => true]);
        foreach ($list as $item) {
            if ($item->getIsSend()) {
                $rawData['Data'][$item->RunnerClassName] = $item->findAnswer($this);
            }
        }
        return $rawData;
    }
}
