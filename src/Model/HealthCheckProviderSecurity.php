<?php

namespace Sunnysideup\HealthCheckProvider\Model;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class HealthCheckProviderSecurity extends DataObject
{
    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Security Check';

    private static $plural_name = 'Security Checks';

    private static $table_name = 'HealthCheckProviderSecurity';

    #######################
    ### Model Section
    #######################

    private static $db = [
        'Secret' => 'Varchar(255)',
        'IpAddress' => 'Varchar(64)',
        'Allowed' => 'Boolean',
        'DefinitelyNotOk' => 'Boolean',
        'AccessCount' => 'Int',
    ];

    private static $has_one = [
        'Editor' => Member::class,
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
        'Editor' => 'Report Editor',
        'EditorID' => 'Report Editor',
    ];

    private static $summary_fields = [
        'Allowed.Nice' => 'Allow',
        'Editor.Title' => 'Editor',
        'Secret' => 'Health Report Data',
        'IpAddress' => 'IP',
        'AccessCount' => 'Access Count',
    ];

    #######################
    ### Casting Section
    #######################

    private static $casting = [
        'Title' => 'Varchar',
    ];

    public static function check(string $key, string $ip): bool
    {
        $obj = self::get_object_from_filter($key, $ip);

        return (bool) $obj->Allowed;
    }

    public static function get_editor_id(string $key, string $ip): int
    {
        $obj = self::get_object_from_filter($key, $ip);

        return (int) $obj->EditorID;
    }

    protected static function get_object_from_filter(string $key, string $ip) : HealthCheckProviderSecurity
    {
        $filter = [
            'Secret' => $key,
            'IpAddress' => $ip,
        ];

        //we make sure we get the last one! Just in case there is more one.
        $obj = HealthCheckProviderSecurity::get()->filter($filter)->last();
        if (! $obj) {
            $obj = HealthCheckProviderSecurity::create($filter);
        }
        $obj->AccessCount++;

        $obj->write();

        return $obj;

    }

    /**
     * casted variable
     * @return string
     */
    public function getTitle(): string
    {
        return 'Retrieval attempt from "' . $this->IpAddress . '" using "' . $this->Secret . '" as key';
    }

    #######################
    ### can Section
    #######################

    public function canCreate($member = null, $context = [])
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
        if (! $this->EditorID) {
            $user = Security::getCurrentUser();
            if($user) {
                $this->EditorID = Security::getCurrentUser()->ID;
            }
        }
        if (! $this->Secret) {
            $this->Secret = 'Careful: no key set - ' . mt_rand(0, 9999999999999999);
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
                ReadonlyField::create('AccessCount', 'Access Count'),
                CheckboxField::create('Allowed', 'Allow this IP with this Key?  If unsure, please double-check!')
                    ->setDescription('Make sure that you are OK with both the key and the IP address to ensure security.'),
                CheckboxField::create('DefinitelyNotOk', 'Check if you think this is a bad request')
                    ->setDescription('Careful, checking this will stop any future retrievals with this key and IP.'),
            ]
        );

        return $fields;
    }
}
