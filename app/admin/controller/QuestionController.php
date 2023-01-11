<?php

namespace app\admin\controller;
use app\common\model\Question;
use app\common\model\QuestionCate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\Cache;
use think\exception\ErrorException;
use vae\controller\AdminCheckAuth;
use think\Db;
use app\common\model\Question as ArticleModel;

class QuestionController extends AdminCheckAuth
{
    public function index()
    {
        return view();
    }

    //列表
    public function getContentList()
    {
    	$param = vae_get_param();
        $where = array();
        if(!empty($param['keywords'])) {
            $where['a.id|a.describution'] = ['like', '%' . $param['keywords'] . '%'];
        }
        if(!empty($param['article_cate_id'])) {
            $where['a.cate_id'] = $param['cate_id'];
        }

        $rows = empty($param['limit']) ? \think\Config::get('paginate.list_rows') : $param['limit'];
        $content = \think\loader::model('Question')
                ->field('a.*,w.title,w.pid')
                ->alias('a')
                ->join('question_cate w','a.cate_id = w.id')
    			->order('a.create_time desc')
                ->where($where)
    			->paginate($rows,false,['query'=>$param])
                ->each(function ($item,$index){
                    $cate = QuestionCate::where(['id' => $item->pid])->find();
                    $item->project = $cate->title;
                });

    	return vae_assign_table(0,'',$content);
    }

    //添加
    public function add()
    {
    	return view();
    }

    //提交添加
    public function addSubmit()
    {
    	if($this->request->isPost()){
    		$param = vae_get_param();
    		$result = $this->validate($param, 'app\admin\validate\Article.add');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                \think\loader::model('Article')->strict(false)->field(true)->insert($param);
                return vae_assign();
            }
    	}
    }

    //修改
    public function edit()
    {
        return view('',['article'=>vae_get_article_info(vae_get_param('id'))]);
    }

    //提交修改
    public function editSubmit()
    {
        if($this->request->isPost()){
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\Article.edit');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                \think\loader::model('Article')->where(['id'=>$param['id']])->strict(false)->field(true)->update($param);
                \think\Cache::clear('VAE_ARTICLE_INFO');
                return vae_assign();
            }
        }
    }

    //软删除
    public function delete()
    {
        $id    = vae_get_param("id");
        if (Db::name('question')->where(['id' => $id])->delete()) {
            return vae_assign(1,"成功放入回收站！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }


    //导入
    public function export() {
        $param = vae_get_param();
        $module = isset($param['module']) ? $param['module'] : 'admin';
        $use = isset($param['use']) ? $param['use'] : 'excel';
        $res = vae_upload($module,$use);
        $filePath = ROOT_PATH.'public'.$res['data'];
        $spreadsheet = IOFactory::load($filePath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        $loginUser = vae_get_login_admin();
        $cate_id = Cache::get($loginUser['id'].'_'.$loginUser['username']);

        $data = [];
        for ($i=1;$i<count($sheetData);$i++) {
            if (empty($sheetData[$i][4]) || empty($sheetData[$i][5]) || empty($sheetData[$i][6])){
                continue;
            }

            $data[] = [
                'cate_id' => $cate_id,
                'number' => $sheetData[$i][0],
                'type' => $sheetData[$i][1],
                'duty' => $sheetData[$i][2],
                'level' => $sheetData[$i][3],
                'describution' => $sheetData[$i][0].$sheetData[$i][4],
                'subject' => $sheetData[$i][5],
                'source' => $sheetData[$i][6],
            ];
        }

        Db::name('question')->where(['cate_id' => $cate_id])->delete();
        $question = new Question();
        $res = $question->saveAll($data);
        return vae_assign(1,'导入成功，请刷新当前页面');
    }

    public function cate()
    {
        return view();
    }

    //列表
    public function getCateContentList()
    {
        $cate = \think\Db::name('question_cate')->order('create_time asc')->select();
        return vae_assign(0,'',$cate);
    }

    //添加
    public function cateAdd()
    {
        return view('',['pid'=>vae_get_param('pid')]);
    }

    //提交添加
    public function cateAddSubmit()
    {
        if($this->request->isPost()){
            $result = $this->validate(vae_get_param(), 'app\admin\validate\ArticleCate.add');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                \think\loader::model('QuestionCate')->cache('VAE_ARTICLE_CATE')->strict(false)->field(true)->insert(vae_get_param());
                return vae_assign();
            }
        }
    }


    //提交修改
    public function cateEditSubmit()
    {
        if($this->request->isPost()) {
            $param = vae_get_param();
            $result = $this->validate($param, 'app\admin\validate\ArticleCate.edit');
            if ($result !== true) {
                return vae_assign(0,$result);
            } else {
                $data[$param['field']] = $param['value'];
                $data['id'] = $param['id'];
                \think\loader::model('QuestionCate')->cache('VAE_ARTICLE_CATE')->strict(false)->field(true)->update($data);
                return vae_assign();
            }
        }
    }

    //软删除
    public function cateDelete()
    {
        $id    = vae_get_param("id");
        $cate_count = \think\Db::name('QuestionCate')->where(["pid" => $id])->count();
        if ($cate_count > 0) {
            return vae_assign(0,"该分类下还有子分类，无法删除！");
        }
        $content_count = \think\Db::name('Question')->where(["cate_id" => $id])->count();
        if ($content_count > 0) {
            return vae_assign(0,"该分类下还有问题描述，无法删除！");
        }
        if (\think\Db::name('QuestionCate')->cache('VAE_ARTICLE_CATE')->delete($id) !== false) {
            return vae_assign(1,"删除分类成功！");
        } else {
            return vae_assign(0,"删除失败！");
        }
    }

    public function search()
    {
        $param = vae_get_param();
        $cate = Db::name('question_cate')->where(['id' => $param['id']])->find();
        if ($cate['pid'] == 0) {
            return  vae_assign(0,'请选择二级分类');
        }

        $loginUser = vae_get_login_admin();
        Cache::set($loginUser['id'].'_'.$loginUser['username'],$param['id']);
        return vae_assign(1,'OK');
    }
}
