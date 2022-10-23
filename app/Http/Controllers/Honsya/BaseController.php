<?php

namespace App\Http\Controllers\Honsya;

use App\Original\Util\SessionUtil;
use App\Models\Base;
use App\Commands\Honsya\Base\ListCommand;
use App\Commands\Honsya\Base\CreateCommand;
use App\Commands\Honsya\Base\UpdateCommand;
use App\Http\Requests\BaseRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;
use App\Lib\Util\DateUtil;

class BaseController extends Controller {

	use tInitSearch;

	/**
	 * コンストラクタ
	 */
	public function __construct(){
		// 表示部分で使うオブジェクトを作成
		$this->initDisplayObj();
		
		// 本社担当者権限を有しないとアクセス不可
		$this->middleware(
			'RoleTentyou',
			['only' => ['getIndex', 'getSearch', 'getSort', 'getEdit', 'putEdit', 'getCreate', 'putCreate']]
		);
	}

	#######################
	## initalize
	#######################
	
	/**
	 * 表示部分で使うオブジェクトを作成
	 * @return [type] [description]
	 */
	public function initDisplayObj(){
		// 表示部分で使うオブジェクトを作成
		$this->displayObj = app('stdClass');
		// カテゴリー名
		$this->displayObj->category = "honsya";
		// 画面名
		$this->displayObj->page = "base";
		// 基本のテンプレート
		$this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
		// コントローラー名
		$this->displayObj->ctl = "Honsya\BaseController";
	}

	#######################
	## 検索・並び替え
	#######################
	
    /**
     * 検索部分のデフォルト値を指定
     * @return [type] [description]
     */
	public function extendSearchParams() {
		$search = array();
		
		// ユーザー情報を取得(セッション)
		$loginAccountObj = SessionUtil::getUser();
		
		if ( !in_array( $loginAccountObj->getRolePriority(), [1,2,3] ) ) {
			$search['base_code'] = $this->selectedBaseCode();
		}
		
		return $search;
	}
	
	#######################
	## 一覧画面
	#######################
	
	/**
	 * 一覧画面のデータを表示
	 * @param  [type] $search     [description]
	 * @param  [type] $sort   [description]
	 * @param  [type] $requestObj [description]
	 * @return [type]             [description]
	 */
	public function showListData( $search, $sort, $requestObj ){
		// 表示するデータを取得
		$showData = $this->dispatch(
			new ListCommand(
				$sort,
				$requestObj
			)
		);
		
        //　表示用に、並び替え情報を取得
        if( isset( $sort['sort'] ) == True && !empty( $sort['sort'] ) == True ){
            foreach ( $sort['sort'] as $key => $value ) {
                // 並び替え情報を格納
                $sortTypes = [
                    'sort_key' => $key,
                    "sort_by" => $value
                ];
            }
        }

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
		return view(
			$this->displayObj->tpl . '.index',
			compact(
				'search',
				'sortTypes',
				'showData',
                'runTime'
			)
		)
		->with( "title", "拠点／一覧" )
		->with( 'displayObj', $this->displayObj );
	}

	#######################
	## 追加画面
	#######################
	
	/**
	 * 登録画面を開く時の画面
	 * @return [type] [description]
	 */
	public function getCreate() {
		// 拠点モデルオブジェクトを取得
		$baseMObj = new Base();

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
		return view(
			$this->displayObj->tpl . '.input',
			compact(
				'baseMObj',
                'runTime'
			)
		)
		->with( "title", "拠点／登録" )
		->with( 'displayObj', $this->displayObj )
		->with( "type", "create" )
		->with( "buttonId", 'regist-button' );
	}

	/**
	 * 登録画面で入力された値を登録
	 * @param  BaseRequest $requestObj [description]
	 * @return [type]                  [description]
	 */
	public function putCreate( BaseRequest $requestObj ) {
		// 登録画面で入力された値を登録

		$this->dispatch(
			new CreateCommand( $requestObj )
		);

		// 一覧画面に画面遷移
		return redirect( action( $this->displayObj->ctl . '@getIndex') );
	}

	#######################
	## 編集画面
	#######################
	
	/**
	 * 編集画面を開く時の画面
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function getEdit( $id ) {
		// 拠点モデルオブジェクトを取得
		$baseMObj = Base::findOrFail( $id );

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
		return view(
			$this->displayObj->tpl . '.input',
			compact(
				'baseMObj',
                'runTime'
			)
		)
		->with( "title", "拠点／編集" )
		->with( 'displayObj', $this->displayObj )
		->with( "type", "edit" )
		->with( "buttonId", 'update-button' );
	}

	/**
	 * 編集画面で入力された値を登録
	 * @param  [type]      $id         [description]
	 * @param  BaseRequest $requestObj [description]
	 * @return [type]                  [description]
	 */
	public function putEdit( $id, BaseRequest $requestObj ) {
		// 編集画面で入力された値を更新
		$this->dispatch(
			new UpdateCommand( $id, $requestObj )
		);
		
		return redirect( action( $this->displayObj->ctl . '@getSearch' ) );
	}

}
