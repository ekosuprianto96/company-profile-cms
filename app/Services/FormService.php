<?php

namespace App\Services;

use App\Forms\InputField;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\FormPageRequest;
use Illuminate\Http\UploadedFile;

class FormService
{
    public function __construct(
        public mixed $page = [],
        protected $section = null,
        protected $request = null,
        protected $inputs = ''
    ) {}

    public function get(?string $key = null)
    {
        if ($key) {
            return $this->page
                ->sections(true)
                ->where('id', $this->section)
                ->first()['forms'][$key] ?? null;
        }

        return $this->page
            ->sections(true)
            ->where('id', $this->section)
            ->first()['forms'] ?? null;
    }

    public function addInput(InputField $input)
    {
        $this->inputs .= $input->render();
        return $this;
    }

    public function renderForm()
    {
        $forms = $this->get();

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

    public function setRequest(Request $request)
    {
        $this->request = $request;

        $validated = $this->validateRequest();

        foreach ($validated as $key => $value) {
            // get file input image
            if ($this->request->hasFile($key)) {
                $getForm = $this->get($key);
                // remove old file
                $this->removeFile($getForm['value'] ?? '', $getForm['path'] ?? 'files');
                $file = $this->saveFile($this->request->file($key), $getForm['path'] ?? 'files');
                $validated[$key] = $file;
            }
        }
        $this->page->setFormSection($this->section, $validated);

        return $this;
    }

    public function saveFile(UploadedFile $file, string $path = 'files')
    {
        $filename = now()->format('ymd') . '-' . Str::uuid() . "." . $file->getClientOriginalExtension();

        $file->move(public_path("assets/images/{$path}"), $filename);

        return $filename;
    }

    public function removeFile(string $filename, string $path = 'files')
    {
        if (file_exists(public_path("assets/images/{$path}/{$filename}"))) {
            unlink(public_path("assets/images/{$path}/{$filename}"));
        }
    }

    public function validateRequest()
    {
        $rules = [];
        $errors = [];
        $forms = $this->get();
        $listName = [];

        if (count($forms) > 0) {
            foreach ($forms as $key => $form) {
                $listName[] = $form['name'];
                if (
                    isset($form['validation']) &&
                    count($form['validation']['rules'] ?? []) > 0
                ) {
                    $rules[$form['name']] = $form['validation']['rules'] ?? [];
                    $errors = [...$form['validation']['errors'] ?? [], ...$errors];
                }
            }
        }

        $this->request->validate($rules, $errors);

        return $this->request->only($listName);
    }

    public function save()
    {
        try {

            $pages = $this->readConfig();
            if (count($pages) > 0) {
                foreach ($pages as $key => $value) {
                    if ($value['id'] != $this->page->id) {
                        continue;
                    }

                    $pages[$key] = $this->page->getConfig();
                    file_put_contents(base_path('config/page.json'), json_encode($pages, JSON_PRETTY_PRINT));

                    // check juga section.json
                    $idSection = collect($pages[$key]['sections'])->pluck('id')->toArray();
                    $sections = (new SectionPageService(sections: $idSection))->save($pages[$key]['sections']);
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function readConfig()
    {
        $pageConfig = file_get_contents(base_path('config/page.json'));
        $toArray = json_decode($pageConfig, true);
        return $toArray;
    }
}
