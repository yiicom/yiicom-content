<?php

namespace yiicom\content\backend\controllers\api\v1;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use yiicom\backend\base\ApiController;
use yiicom\common\traits\ModelTrait;
use yiicom\content\common\grid\UrlAliasColumn;
use yiicom\content\common\models\Category;
use yiicom\content\backend\models\CategorySearch;

class CategoryController extends ApiController
{
    use ModelTrait;

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort->route = '#/content/category/index';

        return $this->renderPartial('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'columns' => $this->getGridColumns(),
        ]);
    }

    /**
     * @return array
     */
    public function getGridColumns()
    {
        return [
            [
                'attribute' => 'id',
                'headerOptions' => ['class' => 'wpx-70'],
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function(Category $model) {
                    return Html::a($model->name, Url::to(['/#/content/category/update', 'id' => $model->id]));
                }
            ],
            [
                'attribute' => 'title',
            ],
            [
                'attribute' => 'alias',
                'class' => UrlAliasColumn::class
            ],
            [
                'attribute' => 'parentId',
                'format' => 'html',
                'filter' => (new Category)->getList(),
                'value' => function(Category $model) {
                    $parent = $model->parent;

                    return $parent
                        ? Html::a($parent->title, Url::to(['/#/content/category/update', 'id' => $parent->id]))
                        : null;
                }
            ],
            [
                'attribute' => 'status',
                'filter' => (new Category)->statusesList(),
                'value' => function(Category $model) {
                    return (new Category)->statusesList()[$model->status];
                }
            ],
        ];
    }

    public function actionFind(int $id = null)
    {
        try {
            return $this->findOrNewModel(Category::class, $id);
        } catch (\Throwable $e) {
            throw new ServerErrorHttpException(Yii::t("yiicom", "Server error: ") . $e->getMessage());
        }
    }

    public function actionSave()
    {
        try {
            /* @var Category $model */
            $id = Yii::$app->request->post('id');
            $model = $this->findOrNewModel(Category::class, $id);

            if ($model->loadAll(Yii::$app->request->post()) && $model->validateAll()) {
                if (! $model->save(false)) {
                    throw new ServerErrorHttpException(Yii::t("yiicom", "Can't save model"));
                }
            }

            return $model;

        } catch (\Throwable $e) {
            throw new ServerErrorHttpException(Yii::t("yiicom", "Server error: ") . $e->getMessage());
        }
    }

    /**
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionDelete()
    {
        try {
            /* @var Category $model */
            $id = Yii::$app->request->post('id');
            $model = $this->findModel(Category::class, $id);

            if ($model->delete()) {
                return ['status' => 'success'];
            }

            throw new ServerErrorHttpException(Yii::t("yiicom", "Can't delete model"));

        } catch (\Throwable $e) {
            throw new ServerErrorHttpException(Yii::t("yiicom", "Server error: ") . $e->getMessage());
        }
    }


}
