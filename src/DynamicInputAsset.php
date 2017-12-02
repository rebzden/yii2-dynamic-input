<?php

namespace rebzden\dynamicinput;


use yii\web\AssetBundle;

class DynamicInputAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = __DIR__ . '/assets';

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
}