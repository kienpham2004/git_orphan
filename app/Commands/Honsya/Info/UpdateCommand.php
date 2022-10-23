<?php

namespace App\Commands\Honsya\Info;

use App\Models\Info;
use App\Commands\Command;
use App\Http\Requests\InfoRequest;

/**
 * お知らせを更新するコマンド
 *
 * @author yhatsutori
 */
class UpdateCommand extends Command{
    
    /**
     * コンストラクタ
     * @param [type]      $id         [description]
     * @param InfoRequest $requestObj [description]
     */
    public function __construct( $id, InfoRequest $requestObj ){
        $this->id = $id;
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 指定したIDのモデルオブジェクトを取得
        $infoMObj = Info::findOrFail( $this->id );

        // 更新する値の配列を取得
        $setValues = $this->requestObj->all();

        // 表示が選択されていない時(非表示の時)
        if( !isset( $setValues["info_view_flg"] ) == True ){
            $setValues["info_view_flg"] = 0;
        }
        
        //
        // 編集されたデータを持つモデルオブジェクトを取得
        $infoMObj->update( $setValues );
        
        // 削除が選択されている時
        if( isset( $setValues["del_flg"] ) == True ){
            $infoMObj->delete();            
        }
        
        return $infoMObj;
        
        /*
        // 備忘録として、別パターンも残してます。
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

        foreach ( $colums as $colum ) {
            $infoMObj->{$colum} = $this->requestObj->{$colum};
        }

        // データの更新
        $infoMObj->save();

        return $infoMObj;
        */
    }

}
