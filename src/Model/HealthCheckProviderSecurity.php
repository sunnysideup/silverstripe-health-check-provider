<?php

namespace Sunnysideup\HealthCheckProvider\Model;

use SilverStripe\Control\Director;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\HTMLReadonlyField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

use Sunnysideup\HealthCheckProvider\Api\SendData;

class HealthCheckProviderSecurity extends DataObject
{

    public static function check($key, $ip) : bool
    {
        $filter = [
            'Secret' => $key,
            'IPAddress' => $ip,
        ];

        //we make sure we get the last one!
        $obj = HealthCheckProviderSecurity::get()->filter($filter)->last();
        if(! $obj) {
            $obj = HealthCheckProviderSecurity::create($filter);
        }
        return (bool) $obj->Allowed;
    }

    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Health Check Security';

    private static $plural_name = 'Health Check Security Items';

    private static $table_name = 'HealthCheckProviderSecurity';

    #######################
    ### Model Section
    #######################

    private static $db = [
        'Secret' => 'Varchar(255)',
        'IpAddress' => 'Text',
        'Allowed' => 'Boolean',
        'DefinitelyNotOk' => 'Boolean',
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
        'Secret' => 'Api Key Provided by Retriever',
        'IpAddress' => 'IP Address of Retriever',
        'Allowed' => 'Allow this key from this IP address?',
    ];

    private static $summary_fields = [
        'Secret' => 'Health Report Data',
        'IpAddress' => 'IP',
        'Allowed.Nice' => 'Allow',
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
        return 'Retrieval attempt from "' . $this->IpAddress.'" using "'.$this->Secret.'" as key';
    }

    #######################
    ### can Section
    #######################


    public function canCreate($member = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function canEdit($member = null)
    {
        return $this->DefinitelyNotOk ? false : parent::canEdit($member);
    }



    #######################
    ### write Section
    #######################

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (! $this->Secret) {
            $this->Secret = 'Careful: no key set - ' . mt_rand(0,9999999999999999);
        }
        if (! $this->IpAddress) {
            $this->IpAddress = 'Careful: no IP Set';
        }
        if ($this->DefinitelyNotOk) {
            $this->Allowed = false;
        }
    }


    #######################
    ### CMS Edit Section
    #######################


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab(
            'Root.Main',
            [
                ReadonlyField::create('Secret', 'Secret Key'),
                ReadonlyField::create('IpAddress', 'IP'),
                CheckboxField::create('Allowed', 'Allow this IP with this Key?  If unsure, please double-check!')
                    ->setDescription('Make sure that you are OK with both the key and the IP address to ensure security.'),
                CheckboxField::create('DefinitelyNotOk', 'Check if you think this is a bad request')
                    ->setDescription('Careful, checking this will stop any future retrievals with this key and IP.'),
            ]
        );

        return $fields;
    }
}
