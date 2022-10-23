<?php

namespace App\Http\Controllers\Honsya;

use App\Original\Util\SessionUtil;
use App\Models\Info;
use App\Commands\Honsya\Info\ListCommand;
use App\Commands\Honsya\Info\CreateCommand;
use App\Commands\Honsya\Info\UpdateCommand;
use App\Http\Requests\InfoRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;
use App\Lib\Util\DateUtil;

class InfoController extends Controller {

	use tInitSearch;

	/**
	 * コンストラクタ
	 */
	public function __construct(){
		// 表示部分で使うオブジェクトを作成
		$this->initDisplayObj();
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
		$this->displayObj->page = "info";
		// 基本のテンプレート
		$this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
		// コントローラー名
		$this->displayObj->ctl = "Honsya\InfoController";
	}

	#######################
	## 一覧画面
	#######################

	/**
	 * 一覧画面のデータを表示
	 * @param  [type] $search  [description]
	 * @param  array $sort    [description]
	 * @param  object $requestObj [description]
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
		->with( "title", "お知らせ／一覧" )
		->with( 'displayObj', $this->displayObj )
		->with( "sortUrl", action( $this->displayObj->ctl . '@getSort' ) );
	}

	#######################
	## 追加画面
	#######################

	/**
	 * 登録画面を開く時の画面
	 * @return [type] [description]
	 */
	public function getCreate(){
		// ユーザー情報を取得(セッション)
		$loginAccountObj = SessionUtil::getUser();

		// お知らせモデルオブジェクトを取得
		$infoMObj = new Info();
        $infoMObj->info_user_id = $loginAccountObj->getUserId();

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
		return view(
			$this->displayObj->tpl . '.input',
			compact(
				'infoMObj',
				'loginAccountObj',
                'runTime'
			)
		)
		->with( "title", "お知らせ／登録" )
		->with( 'displayObj', $this->displayObj )
		->with( "type", "create" )
		->with( "buttonId", 'regist-button' );
	}

	/**
	 * 登録画面で入力された値を登録
	 * @param  BaseRequest $requestObj [description]
	 * @return [type]                  [description]
	 */
	public function putCreate( InfoRequest $requestObj ){
		// 登録画面で入力された値を登録
		$this->dispatch(new CreateCommand( $requestObj ));

		// 一覧画面に画面遷移
		return redirect( action( $this->displayObj->ctl . '@getIndex' ) );
	}

	#######################
	## 編集画面
	#######################

	/**
	 * 編集画面を開く時の画面
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function getEdit( $id ){
		// ユーザー情報を取得(セッション)
		$loginAccountObj = SessionUtil::getUser();
		
		// お知らせモデルオブジェクトを取得
		$infoMObj = Info::findOrFail( $id );

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
		return view(
			$this->displayObj->tpl . '.input',
			compact(
				'infoMObj',
				'loginAccountObj',
                'runTime'
			)
		)
		->with( "title", "お知らせ／編集" )
		->with( 'displayObj', $this->displayObj )
		->with( "type", "edit" )
		->with( "buttonId", 'update-button' );
	}

	/**
	 * 編集画面で入力された値を登録
	 * @param  [type]      $id         [description]
	 * @param  InfoRequest $requestObj [description]
	 * @return [type]                  [description]
	 */
	public function putEdit( $id, InfoRequest $requestObj ){
		// 編集画面で入力された値を更新
		$this->dispatch(
			new UpdateCommand( $id, $requestObj )
		);

		return redirect( action( $this->displayObj->ctl . '@getSearch' ) );
	}

}
