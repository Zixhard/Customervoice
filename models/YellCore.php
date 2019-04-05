<?php
/**
 * Created by PhpStorm.
 * User: zeusprince
 * Date: 8/27/2018 AD
 * Time: 7:59 PM
 */

namespace app\models;

use linslin\yii2\curl\Curl;
use yii\base\ErrorException;
use yii\db\Connection;
use Yii;
use yii\db\Exception;
use yii\db\Query;

class YellCore extends Connection
{
    protected $table;
    protected $lastInsertID;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public function findByField($table,$field = null, $param = null)
    {
        if (!is_null($param)) {
            $rs = (new Query())->select("*")
                ->from($table)
                ->where($field . '=:param', ['param' => $param])
                ->one();
            if($rs) return $rs;
        }
        return false;

    }
    public function findByFields($table,$needFields = '*',$where = null,$order = null, $having = null, $limit = null, $offset = null,$is_all = true){
        $query = (new Query())->select($needFields)
            ->from($table);
        if(!is_null($where)){
            $query->filterWhere($where);
        }
        if(!is_null($order)){
            $query->orderBy($order);
        }
        if(!is_null($having)){
            $query->filterHaving($having);
        }
        if(!is_null($limit)){
            $query->limit($limit);
        }
        if(!is_null($offset)){
            $query->offset($offset);
        }
        return $is_all == true? $query->all() : $query->one();
    }
    public function save($table, $params)
    {
        try{
            $result = Yii::$app->db->createCommand()->insert($table, $params)->execute();
            if($result){
                $this->lastInsertID = Yii::$app->db->getLastInsertID();
                return $result;
            }
        }
        catch (Exception $e){
            echo $e->getMessage();
        }

        return false;
    }
    public function update($table,$columns, $conditions='', $params=array())
    {
        try{
            Yii::$app->db->createCommand()->update($table,$columns,$conditions,$params)->execute();
            return true;
        }
        catch (ErrorException $e){
            return $e->getMessage();
        }

    }
    public function getLastInsertId($sequence = null)
    {
        return $this->lastInsertID;
    }
    public function currentDateTime()
    {
        self::setTimeZone();
        $date = date('Y-m-d H:i:s');
        return $date;

    }
    public function currentDate($format = 'Y-m-d')
    {
        self::setTimeZone();
        $date = date($format);
        return $date;

    }
    public function getLastDay(){
        $a_date = self::currentDateTime();
        $date = new \DateTime($a_date);
        $date->modify('last day of this month');
        return $date->format('Y-m-d');
    }
    public function getFistDay(){
        $a_date = self::currentDateTime();
        $date = new \DateTime($a_date);
        $date->modify('first day of this month');
        return $date->format('Y-m-d');
    }
    public function setTimeZone()
    {
        Yii::$app->setTimeZone('Asia/Bangkok');
    }
    function prettyDate($time) {
        $today = strtotime(date('M j, Y'));
        $reldays = ($time - $today)/86400;
        if ($reldays >= 0 && $reldays < 1) {
            return 'Today';
        } else if ($reldays >= 1 && $reldays < 2) {
            return 'Tomorrow';
        } else if ($reldays >= -1 && $reldays < 0) {
            return 'Yesterday';
        }

        if (abs($reldays) < 7) {
            if ($reldays > 0) {
                $reldays = floor($reldays);
                return 'In ' . $reldays . ' day' . ($reldays != 1 ? 's' : '');
            } else {
                $reldays = abs(floor($reldays));
                return $reldays . ' day' . ($reldays != 1 ? 's' : '') . ' ago';
            }
        }

        if (abs($reldays) < 182) {
            return date('l, j F',$time ? $time : time());
        } else {
            return date('l, j F, Y',$time ? $time : time());

        }

    }
    public function imageOptimizer($source = null ,$destination = null, $quality = 80){
        try{
            $info = getimagesize($source);
            if($info){
                switch ($info['mime']){
                    case 'image/jpeg' :
                        $image = imagecreatefromjpeg($source);
                        break;
                    case  'image/gif' :
                        $image = imagecreatefromgif($source);
                        break;
                    case 'image/png' :
                        $image = imagecreatefrompng($source);
                        break;
                    default :
                        throw new \yii\base\Exception('Not Allow '+$info['mime']);
                        break;
                }

                if(imagejpeg($image,$destination,$quality)){
                    return $destination;
                }
            }
        }
        catch (ErrorException $e){
            return $e->getMessage();
        }

    }
    public function sendSMS($to,$from,$message){
        $to = preg_replace("/^0/","66",$to);
        $curl = new Curl();
        $params = [
            'from' => $from,
            'to' => $to,
            'text' => $message
        ];
        $curl->setHeaders(
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode('yellkurobuta:yellyell')
            ]
        );
        $curl->setRequestBody(json_encode($params));
        $result = $curl->post(
            'http://api.ants.co.th/sms/1/text/single'
            );

    }

}