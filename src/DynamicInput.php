<?php

namespace rebzden\dynamicinput;


use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\HtmlNode;
use PHPHtmlParser\Dom\Tag;
use ReflectionClass;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;
use yii\widgets\InputWidget;

/**
 * Class DynamicInput
 *   <?php $dropdownsInput = DynamicInput::begin([
 *          'form'           => $form,
 *          'model'          => $model,
 *          'attribute'      => 'dropdowns',
 *          'attributeClass' => Page::class
 * ]) ?>
 * <div class="row">
 *      <div class="col-md-6">
 *          <?php $dropdownsInput->startTemplate() ?>
 *          <div class="row">
 *              <div class="col-md-6">
 *                  <?= $dropdownsInput->field('name')->textInput() ?>
 *              </div>
 *              <div class="col-md-6">
 *                  <?= $dropdownsInput->field('id')->textInput() ?>
 *              </div>
 *              <div class="col-md-6">
 *                  <?= $dropdownsInput->removeButton() ?>
 *              </div>
 *          </div>
 *          <?php $dropdownsInput->endTemplate() ?>
 *      </div>
 *      <div class="col-md-6">
 *          <?= $dropdownsInput->addButton() ?>
 *      </div>
 * </div>
 *
 * <?php DynamicInput::end() ?>
 * @package common\components\widgets\dynamicinput
 */
class DynamicInput extends InputWidget
{
    private $arrayModel = null;

    /**
     * @var ActiveForm
     */
    public $form;

    public $template;

    public $inputTemplate = '.dynamic-input';

    public $attributeClass = "";
    public $renderFirstElement = true;
    public $startIndex = 0;
    public $attributeArray = [];

    /**
     * @var Dom
     */
    private $dom;

    public function getId($autoGenerate = true)
    {
        return parent::getId($autoGenerate) . $this->attribute;
    }

    public function startTemplate()
    {
        echo Html::beginTag('div', ['class' => 'dynamic-input-container-' . $this->id]);
        ob_start();
    }

    public function endTemplate()
    {
        $this->template = Html::tag('div', ob_get_clean(), ['class' => 'dynamic-input-' . $this->id]);
        $this->renderModels();
        echo Html::endTag('div');
        $this->renderTemplate();
    }

    public function init()
    {
        parent::init();
        $this->dom = new Dom;
        $this->arrayModel = new $this->attributeClass;
    }

    /**
     * @param $attribute
     * @param int $id
     * @param array $options
     * @return \yii\widgets\ActiveField
     */
    public function field($attribute, $id = 0, $options = [])
    {
        return $this->form->field($this->arrayModel, "[{$id}]{$attribute}", $options);
    }

    public function addButton($options = [])
    {
        return Html::button("Add", ['type' => "button", 'class' => 'btn btn-success add-button', 'data-id' => $this->startIndex]);
    }

    public function removeButton($options = [])
    {
        return Html::button("Remove", ['type' => "button", 'class' => 'btn btn-danger remove-button', 'data-id' => $this->startIndex]);
    }

    public function run()
    {
        $this->registerAssets();
        parent::run();
    }

    private function registerAssets()
    {
        $view = $this->getView();
        DynamicInputAsset::register($view);
        $options = Json::encode([
            'widgetId'     => $this->id,
            'currentIndex' => $this->startIndex,
            'form'         => $this->form->id,
            'className'    => (new ReflectionClass($this->attributeClass))->getShortName()
        ]);
        $view->registerJs("var {$this->id}{$this->attribute} = new DynamicInput({$options})");
    }

    private function renderModels()
    {
        $modelAttribute = $this->attribute;
        if ($this->model->$modelAttribute) {
            foreach ($this->model->$modelAttribute as $index => $attribute) {
                $this->startIndex = $index;
                $this->renderInputTemplate($attribute);
            }
        } elseif ($this->renderFirstElement) {
            $this->renderInputTemplate($this->arrayModel);
        }

    }

    private function renderInputTemplate($model)
    {
        $this->dom->load($this->template);
        $templateRoot = $this->dom->find('.dynamic-input-' . $this->id)[0];
        /** @var HtmlNode $templateRoot */
        $templateRoot->setAttribute('data-id', $this->startIndex);
        $formGroups = $this->dom->find('.form-group');
        /** @var HtmlNode $formGroup */
        foreach ($formGroups as $formGroup) {
            /** @var HtmlNode $inputLabel */
            /** @var HtmlNode $formInput */
            $inputLabel = $formGroup->find('label');
            if ($inputLabel) {
                $inputLabel->setAttribute('for', $this->changeId($inputLabel->getAttribute('for'), $this->startIndex));
            }
            $formInput = $formGroup->find('input, select, textarea')[0];
            if ($formInput) {
                $oldInputId = $formInput->getAttribute('id');
                $formInput->setAttribute('name', $this->changeName($formInput->getAttribute('name'), $this->startIndex));
                $formInput->setAttribute('id', $this->changeId($formInput->getAttribute('id'), $this->startIndex));
                $formGroup->setAttribute('class', str_replace($oldInputId, $formInput->getAttribute('id'), $formGroup->getAttribute('class')));
                $modelAttribute = $this->getAttributeFromId($formInput->getAttribute('id'));
                $formInput->setAttribute('value', $model->$modelAttribute);
            }
        }
        $buttons = $this->dom->find('.remove-button, .add-button');
        /** @var HtmlNode $button */
        foreach ($buttons as $button) {
            $button->setAttribute('data-id', $this->startIndex);
        }
        echo $this->dom->outerHtml;
    }

    private function changeId($idString, $id = 1)
    {
        $re = '/([-\d-]{1,})/i';
        $replacedId = preg_replace_callback($re, function () use ($id) {
            return "-{$id}-";
        }, $idString);
        return $replacedId;
    }

    private function changeName($idString, $id = 1)
    {
        $re = '/(\[\d{1,}\])/i';
        $replacedId = preg_replace_callback($re, function () use ($id) {
            return "[{$id}]";
        }, $idString);
        return $replacedId;
    }

    private function renderTemplate()
    {
        echo Html::tag('template',
            $this->template, ['class' => 'dynamicTemplate-' . $this->id]
        );
    }

    private function getAttributeFromId($idString)
    {
        $re = '/\w+$/';
        preg_match($re, $idString, $matches);
        return ArrayHelper::getValue($matches, 0);
    }
}