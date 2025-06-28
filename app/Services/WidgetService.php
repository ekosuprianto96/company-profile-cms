<?php

namespace App\Services;

use App\Forms\InputField;
use App\Repositories\WidgetRepository;

class WidgetService
{
    public function __construct(
        private $inputs = '',
        private WidgetRepository $widget
    ) {}

    public function getAllWidgets()
    {
        return $this->widget->getAllWidgets();
    }

    public function getForms()
    {
        return $this->widget->getAllWidgets();
    }

    public function addInput(InputField $input)
    {
        $this->inputs .= $input->render();
        return $this;
    }

    public function renderForm()
    {
        $forms = $this->getForms();

        if (count($forms ?? []) > 0) {
            foreach ($forms as $key => $value) {
                $this->addInput(
                    new InputField(
                        type: $value['type'],
                        attributes: $value,
                        callback: function ($input) {
                            if (isset($input->attributes['model'])) {
                                $model = $input->model();

                                if (count($input->attributes['params'] ?? []) > 0) {
                                    $model = $model->where($input->attributes['params']);
                                }

                                $collect = $model->get();
                                $input->setCollection($collect);
                                return true;
                            }

                            return false;
                        }
                    )
                );
            }
        }

        return $this->inputs;
    }
}
