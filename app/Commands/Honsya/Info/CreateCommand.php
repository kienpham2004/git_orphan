<?php

namespace App\Commands\Honsya\Info;

use App\Models\Info;
use App\Commands\Command;
use App\Http\Requests\InfoRequest;

/**
 * お知らせを新規作成するコマンド
 *
 * @author yhatsutori
 */
class CreateCommand extends Command{
    
    /**
     *  コンストラクタ
     * @param InfoRequest $requestObj [description]
     */
    public function __construct( InfoRequest $requestObj ){
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 追加する値の配列を取得
        $setValues = $this->requestObj->all();

        // 登録されたデータを持つモデルオブジェクトを取得
        $infoMObj = Info::create( $setValues );

        return $infoMObj;
        
        /*
        // 備忘録として、別パターンも残してます。
        $infoMObj = new Info();
        // 更新を行うカラム名
        $colums = [
            'info_target_date',
            'info_user_id',
            'info_view_flg',
            'info_view_date_min',
            'info_view_date_max',
            'info_title',
            'info_body'
        ];

        foreach ($colums as $colum) {
            $infoMObj->{$colum} = $this->requestObj->{$colum};
        }

        $infoMObj->save();

        return $infoMObj;
        */
    }
    
}
