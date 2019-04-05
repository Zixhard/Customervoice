<?php
/**
 * Created by PhpStorm.
 * User: zeusprince
 * Date: 29/10/2018 AD
 * Time: 11:16
 */

namespace app\models;
use Yii;

class Feedback extends YellCore
{
    public function saveFeedback($name,$feedback,$photo,$time,$visible){
        $result = $this->save(
            'tbl_feedback',
            [
                'name' => $name,
                'feedback' => $feedback,
                'photo' => $photo,
                'time' => $time,
                'visible' => $visible
            ]
        );
        if($result == true){
            return true;
        }
        return false;
    }
    public function getFeedback(){
        if(Yii::$app->session->get('user')){
            $user = Yii::$app->session->get('user');
            $sql = "SELECT * FROM tbl_feedback WHERE visible = '1'
                    UNION
                    SELECT * FROM tbl_feedback WHERE name = '".$user."' and visible = '0'
                    ORDER BY time";

            $result = Yii::$app->db->createCommand($sql)->queryAll();
        }else{
            $result = $this->findByFields('tbl_feedback','*',['visible' => '1'],  'time asc'     ,null);
        }

        $feedback = [];
        $lastID = null;
        if($result){
            foreach ($result as $key => $value){
                $align = $key%2==1?'right' : 'left';
                array_push($feedback,[
                    'name' => $value['id'],
                    'avatar' => $value['photo'],
                    'msg' => addslashes($value['feedback']),
                    'delay' => 0,
                    'align' => $align,
                    'showTime' => true,
                    'time' => $this->prettyDate(strtotime($value['time']))
                ]);
                $lastID = $value['id'];
            }
            return $feedback;
        }
        return false;
    }

}