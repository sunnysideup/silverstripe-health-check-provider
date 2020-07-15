<?php

namespace Sunnysideup\HealthCheckProvider\Model;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\GroupedList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use Sunnysideup\CMSNiceties\Traits\CMSNicetiesTraitForCMSLinks;
use Sunnysideup\CMSNiceties\Traits\CMSNicetiesTraitForRightTitles;
use Sunnysideup\HealthCheckProvider\Admin\HealthCheckAdmin;
use Sunnysideup\HealthCheckProvider\Tasks\HealthCheckAnswerJob;

class HealthCheck extends DataObject
{
    use CMSNicetiesTraitForRightTitles;
    use CMSNicetiesTraitForCMSLinks;

    /**
     * use exec to run jobs
     * @var bool
     */
    private static $use_exec = false;

    #######################
    ### Names Section
    #######################

    private static $singular_name = 'health check provider Report';

    private static $plural_name = 'health check provider Reports';

    private static $table_name = 'HealthCheck';

    #######################
    ### Model Section
    #######################

    private static $db = [
        'Sent' => 'Boolean',
    ];

    private static $has_one = [
        'Editor' => Member::class,
    ];

    private static $has_many = [
        'HealthCheckAnswers' => HealthCheckAnswer::class,
    ];

    #######################
    ### Further DB Field Details
    #######################

    private static $cascade_deletes = [
        'HealthCheckAnswers',
    ];

    private static $default_sort = [
        'Created' => 'DESC',
    ];

    #######################
    ### Field Names and Presentation Section
    #######################

    private static $field_labels = [
        'HealthCheckAnswers' => 'Answers',
    ];

    private static $field_labels_right = [
        'Sent' => 'Has been sent.',
    ];

    private static $summary_fields = [
        'Published.Nice' => 'Published',
        'IsCurrent.Nice' => 'Is Current',
        'Created.Nice' => 'Created',
        'LastEdited.Nice' => 'Last Edited',
        'Editor.Title' => 'Editor',
        'HealthCheckAnswers.Count' => 'Answers',
    ];

    #######################
    ### Casting Section
    #######################

    private static $casting = [
        'Title' => 'Varchar',
        'IsCurrent' => 'Boolean',
        'HasCompleted' => 'Boolean',
        'HasErrors' => 'Boolean',
    ];

    private static $primary_model_admin_class = HealthCheckAdmin::class;

    /**
     * casted variable
     * @return string
     */
    public function getTitle(): string
    {
        return 'Check carried out: ' . $this->dbObject('Created')->Nice();
    }

    /**
     * casted variable
     * @return bool
     */
    public function getIsCurrent(): bool
    {
        $id = 0;
        $item = self::current_check();
        if ($item) {
            $id = $item->ID;
        }
        return $this->ID === $id;
    }

    public function getHasErrors()
    {
        $list = $this->HealthCheckAnswers();
        $val = false;
        if ($list instanceof DataList) {
            $val = $this->HealthCheckAnswers()->where('Error IS NOT NULL AND Error <> \'\'')->count() ? false : true;
        }
        return DBField::create_field('Boolean', $val);
    }

    /**
     * @return mixed
     */
    public static function current_check()
    {
        /** @var HealthCheck|null */
        return DataObject::get_one(HealthCheck::class, ['Sent' => true]);
    }

    public function i18n_singular_name()
    {
        return _t(self::class . '.SINGULAR_NAME', 'health check provider Report');
    }

    public function i18n_plural_name()
    {
        return _t(self::class . '.PLURAL_NAME', 'health check provider Reports');
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

    #######################
    ### write Section
    #######################

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (! $this->EditorID) {
            $this->EditorID = Security::getCurrentUser()->ID;
        }
        //...
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $list = HealthCheckItem::get()->filter(['Enabled' => true])
            ->sort('HealthCheckItem.HealthCheckGroupSortOrder ASC, HealthCheckItem.SortOrder ASC');
        foreach ($list as $item) {
            $item->createOrFindAnswer($this);
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
                ReadonlyField::create('Created'),
                ReadonlyField::create('LastEdited'),
                ReadonlyField::create('HasErrorsNice', 'Errors occured in running checks', $this->getHasErrors()->Nice()),
            ]
        );
        $fields->addFieldsToTab(
            'Root.Output',
            [
                LiteralField::create(
                    'Output',
                    '<pre>'.$this->getData().'</pre>'
                ),
            ]
        );

        return $fields;
    }

    public function getRawData() : array
    {

    }

    public function getData() : string
    {
        return json_encode($this->getRawData());
    }

}
