<?php

namespace Sunnysideup\HealthCheck\Model;

use SilverStripe\Core\ClassInfo;

use SilverStripe\Core\Injector\Injector;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\Filters\ExactMatchFilter;
use SilverStripe\ORM\Filters\PartialMatchFilter;



use Sunnysideup\HealthCheck\Admin\HealthCheckAdmin;
use Sunnysideup\HealthCheck\Checks\HealthCheckItemRunner;
use Sunnysideup\HealthCheck\Interfaces\HealthCheckItemInterface;

class HealthCheckItemProvider extends DataObject implements HealthCheckItemInterface
{
    protected $runner = null;

    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Piece of Info';

    private static $plural_name = 'Pieces of Info';

    private static $table_name = 'HealthCheckItemProvider';

    #######################
    ### Model Section
    #######################

    private static $belongs_many_many = [
        'HealthCheckProviders' => HealthCheckProvider::class,
    ];

    private static $db = [
        'Send' => 'Boolean',
        'RunnerClassName' => 'Varchar(255)',
    ];

    #######################
    ### Further DB Field Details
    #######################

    private static $searchable_fields = [
        'Send' => ExactMatchFilter::class,
        'RunnerClassName' => PartialMatchFilter::class,
    ];

    #######################
    ### Field Names and Presentation Section
    #######################

    private static $field_labels = [
        'Send' => 'Are you happy to send this?',
        'RunnerClassName' => 'Code',
    ];

    // private static $field_labels_right = [];

    private static $summary_fields = [
        'RunnerClassName' => 'Code',
        'Send.Nice' => 'Send',
        'CalculatedAnswer' => 'Data',
    ];

    #######################
    ### Casting Section
    #######################

    private static $casting = [
        'CalculatedAnswer' => 'Text',
        'Title' => 'Varchar',
    ];

    private static $primary_model_admin_class = HealthCheckAdmin::class;

    public function i18n_singular_name()
    {
        return $this->Question . ' ' . _t(self::class . '.SINGULAR_NAME', 'Piece of Info');
    }

    public function i18n_plural_name()
    {
        return $this->Question . ' ' . _t(self::class . '.PLURAL_NAME', 'Pieces of Info');
    }

    public function getTitle()
    {
        return DBField::create_field('HTMLText', ClassInfo::shortName($this->RunnerClassName));
    }

    #######################
    ### can Section
    #######################

    public function canEdit($member = null)
    {
        return false;
    }

    public function canCreate($member = null, $context = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return class_exists($this->RunnerClassName) ? false : parent::canDelete($member);
    }

    #######################
    ### write Section
    #######################

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        foreach (HealthCheckItemProvider::get() as $item) {
            if (! class_exists($item->RunnerClassName)) {
                DB::alteration_message('Deleting superfluous: ' . $item->getDefaultQuestion(), 'deleted');
                $item->delete();
            }
        }

        $classes = ClassInfo::subclassesFor(HealthCheckItemRunner::class, false);
        $ids = [0 => 0];
        foreach ($classes as $className) {
            $filter = ['RunnerClassName' => $className];
            $obj = DataObject::get_one(HealthCheckItemProvider::class, $filter);
            if (! $obj) {
                $obj = HealthCheckItemProvider::create($filter);
                DB::alteration_message('Creating Health Check: ' . $obj->getDefaultQuestion(), 'created');
            }
            $id = $obj->write();
            if (! $id) {
                $id = $obj->ID;
            }
            $ids[$id] = $id;
        }
        $badOnes = HealthCheckItemProvider::get()->where('HealthCheckItemProvider.ID NOT IN (' . implode(',', $ids) . ')');
        foreach ($badOnes as $badOne) {
            DB::alteration_message('Deleting superfluous: ' . $badOne->getDefaultQuestion(), 'deleted');
            $badOne->delete();
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $this->addRightTitles($fields);

        return $fields;
    }

    public function getRunner()
    {
        if (! $this->runner) {
            $class = $this->RunnerClassName;
            if (class_exists($class)) {
                $this->runner = Injector::inst()->get($this->RunnerClassName, $asSingleton = true, [$this]);
            }
        }
        return $this->runner;
    }

    public function findAnswer(): array
    {
        return [
            'Answer' => $this->getRunner()->getCalculatedAnswer(),
            'IsInstalled' => $this->getRunner()->getIsInstalled(),
            'IsEnabled' => $this->getRunner()->getIsEnabled(),
        ];
    }
}
