<?php

namespace App\Commands\Honsya\User;

use App\Lib\Util\DateUtil;
use App\Models\UserAccount;
use App\Http\Requests\UserRequest;
use App\Commands\Command;

/**
 * 担当者を更新するコマンド
 *
 * @author yhatsutori
 */
class UpdateCommand extends Command{

    /**
     * コンストラクタ
     * @param [type]      $id         [description]
     * @param UserRequest $requestObj [description]
     * @param [type]      $file_name  [description]
     */
    public function __construct( $id, UserRequest $requestObj, $file_name='' ){
        $this->id = $id;
        $this->requestObj = $requestObj;
        // 顔写真用ファイル名
        $this->file_name = $file_name;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 指定したIDのモデルオブジェクトを取得
        $userMObj = UserAccount::getStaffbyId( $this->id );
        // 元々登録されている画像のパスを一時的に保持する
        $tmp_image_path = $userMObj->file_name;

        // 更新する値の配列を取得
        $setValues = $this->requestObj->all();

        // 更新を行うカラム名
        $colums = [
            'user_code',
            'user_login_id',
            //'password',
            'user_name',
            'user_password',
            'base_id',
            'file_name',
            'comment',
            'account_level',
            'bikou'
        ];

        foreach ( $colums as $colum ) {
            // 画像の時の処理
            if ( $colum == 'file_name' ) {
                if (! empty($this->file_name)) { // 顔画像が選択された時
                    $userMObj->{$colum} = "FaceImages/{$this->file_name}";
                    $userMObj->img_uploaded_flg = 1;
                    $userMObj->img_uploaded_at = DateUtil::now();

                } else { // 顔画像が選択されなかった時
                    $userMObj->{$colum} = $tmp_image_path; // 一時的に保持したパスを再度代入
                }

            } else {
                $userMObj->{$colum} = $this->requestObj->{$colum};

            }
        }

        // 削除が選択なしの場合
        if( !isset( $setValues["del_flg"] ) == True ){
            $userMObj->deleted_at = null;
        }

        // データの更新
        $userMObj->save();
        
        // 削除が選択されている時
        if( isset( $setValues["del_flg"] ) == True ){
            $userMObj->delete();            
        }
        
        return $userMObj;
    }
    
}
