<?php

namespace Sunnysideup\HealthCheckProvider\Model;

use Exception;
use SilverStripe\Core\ClassInfo;

use SilverStripe\Core\Injector\Injector;

use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\Filters\ExactMatchFilter;
use SilverStripe\ORM\Filters\PartialMatchFilter;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class HealthCheckItemProvider extends DataObject
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
        'Include' => 'Boolean',
        'RunnerClassName' => 'Varchar(255)',
    ];

    #######################
    ### Further DB Field Details
    #######################

    private static $defaults = [
        'Include' => true,
    ];

    private static $searchable_fields = [
        'Include' => ExactMatchFilter::class,
        'RunnerClassName' => PartialMatchFilter::class,
    ];

    #######################
    ### Field Names and Presentation Section
    #######################

    private static $field_labels = [
        'Include' => 'Are you happy to send this?',
        'RunnerClassName' => 'Code',
        'HealthCheckProviders' => 'Reports',
    ];

    private static $summary_fields = [
        'CodeNice' => 'Code',
        'Include.Nice' => 'Include',
        'AnswerSummary' => 'Data',
    ];

    #######################
    ### Casting Section
    #######################

    private static $casting = [
        'AnswerSummary' => 'HTMLText',
        'Title' => 'Varchar',
        'Code' => 'Varchar',
        'CodeNice' => 'Varchar',
    ];

    public function getTitle()
    {
        return DBField::create_field('HTMLText', $this->getCode());
    }

    public function getCode(): string
    {
        return ClassInfo::shortName($this->RunnerClassName);
    }

    public function getCodeNice(): string
    {
        return preg_replace('/([a-z])([A-Z])/s', '$1 $2', $this->getCode());
    }

    public function getAnswerSummary()
    {
        $data = $this->findAnswer();
        $data = $this->summariseData($data);

        return DBField::create_field('HTMLText', '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>');
    }

    #######################
    ### can Section
    #######################

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
                DB::alteration_message('Deleting superfluous: ' . $item->getTitle(), 'deleted');
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
                DB::alteration_message('Creating Health Check: ' . $obj->getTitle(), 'created');
            }
            $id = $obj->write();
            if (! $id) {
                $id = $obj->ID;
            }
            $ids[$id] = $id;
        }
        $badOnes = HealthCheckItemProvider::get()->where('HealthCheckItemProvider.ID NOT IN (' . implode(',', $ids) . ')');
        foreach ($badOnes as $badOne) {
            DB::alteration_message('Deleting superfluous: ' . $badOne->getTitle(), 'deleted');
            $badOne->delete();
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('HealthCheckProviders');
        $fields->removeByName('RunnerClassName');
        $fields->addFieldsToTab(
            'Root.Main',
            [
                ReadonlyField::create('CodeNice', 'Code'),
                ReadonlyField::create('AnswerSummary', 'Answer'),
            ]
        );

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
        try {
            $answer = $this->getRunner()->getCalculatedAnswer();
        } catch (Exception $exception) {
            $answer = 'Caught exception: ' . $exception->getMessage();
        }
        return [
            'Answer' => $answer,
            'IsInstalled' => $this->getRunner()->IsInstalled(),
            'IsEnabled' => $this->getRunner()->IsEnabled(),
        ];
    }

    private function summariseData($mixed)
    {
        if (is_string($mixed)) {
            if (strlen($mixed) > 50) {
                return substr($mixed, 0, 50) . '...';
            }
        } elseif (is_array($mixed)) {
            $returnArray = [];
            $count = 0;
            foreach ($mixed as $key => $item) {
                $count++;
                $returnArray[$this->summariseData($key)] = $this->summariseData($item);
                if ($count > 3) {
                    $returnArray[] = ' + ' . count($mixed) . ' MORE ...';
                    break;
                }
            }
            return $returnArray;
        }
        return $mixed;
    }
}
