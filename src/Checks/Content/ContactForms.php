<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Content;

use Sunnysideup\HealthCheckProvider\Checks\HealthCheckItemRunner;

class ContactForms extends HealthCheckItemRunner
{
    private static $contact_form_classes = [
        'SilverStripe\\UserForms\\Model\\UserDefinedForm',
    ];

    public function getCalculatedAnswer(): array
    {
        $classesToCheck = $this->Config()->get('contact_form_classes');
        $listOfContactFormLinks = [];
        foreach ($classesToCheck as $className) {
            if (class_exists($className)) {
                $pages = $className::get();
                $listOfContactFormLinks = array_merge(
                    $listOfContactFormLinks,
                    $this->turnPagesIntoArray($pages)
                );
            }
        }
        return array_values($listOfContactFormLinks);
    }

    protected function nameSpacesRequired(): array
    {
        $array = [];
        foreach ($this->Config()->get('contact_form_classes') as $className) {
            $explode = explode('\\', $className);
            if (count($explode) === 1) {
                $array[] = $className;
            } elseif (count($explode) > 2) {
                $array[] = $className[0] . '\\' . $className[1];
            }
        }

        return $array;
    }
}
