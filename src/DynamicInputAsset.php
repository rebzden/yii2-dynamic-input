<?php

namespace common\components\widgets\dynamicinput;


use common\components\web\AssetBundle;

class DynamicInputAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public $sourcePath = '@common/components/widgets/dynamicinput/assets';

    /**
     * @inheritdoc
     */
    public $css = [
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/dynamicinput.js'
    ];

    /**
     *
     * @inheritdoc
     */
    public $depends = [
        'yii\web\YiiAsset'
    ];

    public $publishOptions = [
        'forceCopy' => true,
    ];

}