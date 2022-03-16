<?php

declare(strict_types=1);

namespace bufanyun\extend\utils;

/**
 * 数组帮助类
 */
class ArrayUtil
{
    /**
     * 将一个数字分成N份
     * numberRandom
     * @param int $total_money  总数
     * @param int $total_num 拆分次数
     * @example '$total_money = 10, $total_num = 3, return = 2,5,3'
     * @return array
     */
    public static function numberRandom(int $total_money, int $total_num): array
    {
        $total_money = $total_money - $total_num;
        $data        = [];
        for ($i = $total_num; $i > 0; $i--) {
            $data[$i] = 1;
            $ls_money = 0;
            if ($total_money > 0) {
                if ($i == 1) {
                    $data[$i] += $total_money;
                } else {
                    $max_money = floor($total_money / $i);
                    $ls_money  = mt_rand(0, $max_money);
                    $data[$i]  += $ls_money;
                }
            }
            $total_money -= $ls_money;
        }
        return $data;
    }

    /**
     * 删除多维数组PHP中的所有特定键
     * removeKey
     * @param array $array
     * @param $key
     * @return array
     */
    public static function removeKey(array $array, $key): array
    {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $array[$k] = self::removeKey($v, $key);
            } elseif ($k == $key) {
                unset($array[$k]);
            }
        }
        return $array;
    }

    /**
     * 将多维数组的指定 键名转为一维数组
     * getShiftOne
     * @param array $array
     * @param string $keyName
     * @return array
     */
    public static function getShiftOne(array $array, $keyName = 'id')
    {
        $arr = [];
        foreach ($array as $k => $v) {
            if (is_array($v[$keyName])) {
                return self::getShiftOne($v[$keyName], $keyName);
            }
            //            array_push($arr,$v[$keyName]);
            $arr[] = $v[$keyName];
        }

        return $arr;
    }

    /**
     * 获取二维数组中指定键的对应索引
     * @param array $array
     * @param $keyName
     * @param $key
     * @return false|int|string
     */
    public static function getSelectIndex(array $array, $keyName, $key)
    {
        $id        = array_column($array, $keyName);
        return array_search($key, $id);
    }

    /**
     * 获取二维数组中指定键的值
     * @param  array  $array
     * @param $keyName
     * @param $key
     * @param $valueName
     *
     * @return mixed|string
     */
    public static function getSelectValue(array $array, $keyName, $key, $valueName)
    {
        //重新排序，保证索引一致性
        $array = array_merge($array);

        $found_key = self::getSelectIndex($array, $keyName, $key);
        if ($found_key === false) {
            return '';
        }

        $value = is_array($array[$found_key]) ? $array[$found_key] : [];
        return $value[$valueName] ?? '';
    }

    /**
     * 多维数组转二维
     * mapToArray
     * @param $array
     * @param $key
     * @param $value
     * @return array
     */
    public static function mapToArray(array $array, $key, $value): array
    {
        $arr = [];
        foreach ($array as $k => $v) {
            $arr[$v[$key]] = $v[$value];
        }
        unset($v);
        return $arr;
    }

    /**
     * 经典抽奖算法
     * luckDraw
     *
     * @param array $prize_arr 参与抽奖人员数据
     *    $prize_arr = array(
     * '0' => array('id'=>1,'name'=>'小王','v'=>1),
     * '1' => array('id'=>2,'name'=>'小李','v'=>5),
     * '2' => array('id'=>3,'name'=>'小张','v'=>10),
     * '3' => array('id'=>4,'name'=>'小二','v'=>12),
     * '4' => array('id'=>5,'name'=>'小菜','v'=>22),
     * '6' => array('id'=>6,'name'=>'小范','v'=>50),
     * '7' => array('id'=>7,'name'=>'小范01','v'=>50),
     * '8' => array('id'=>8,'name'=>'小范02','v'=>100),
     * '9' => array('id'=>9,'name'=>'小范03','v'=>50),
     * '10' => array('id'=>10,'name'=>'小范04','v'=>50),
     * '11' => array('id'=>11,'name'=>'小范05','v'=>50),
     * '12' => array('id'=>12,'name'=>'小范06','v'=>50),
     * '13' => array('id'=>13,'name'=>'小范07','v'=>50),
     * '14' => array('id'=>14,'name'=>'小范08','v'=>50),
     * '15' => array('id'=>15,'name'=>'小范09','v'=>100),
     * '16' => array('id'=>16,'name'=>'小范10','v'=>100),
     * );
     * @param int $count 中奖人数量
     * @param bool $is_status 是否开启概率为100必中: 默认开启
     *
     * @return array
     */
    public static function luckDraw($prize_arr = [], $count = 1, $is_status = true)
    {
        foreach ($prize_arr as $key => $val) {
            $arr[$key] = $val['v'];
        }
        unset($val);

        $temp_rest = [];
        for ($i = 0; $i < $count; $i++) {
            $rid         = self::randomDraw($arr, $is_status); //根据概率获取人员ID
            $temp_rest[] = $prize_arr[$rid];                   //中奖项
            unset($prize_arr[$rid]);
            unset($arr[$rid]);
        }

        return ['success' => $temp_rest, 'fail' => $prize_arr];
    }

    /**
     * 随机抽取一个中奖者
     * randomDraw
     *
     * @param $proArr
     * @param bool $is_status
     *
     * @param int $retry
     *
     * @return int|string
     */
    public static function randomDraw($proArr, $is_status = true, $retry = 1)
    {
        $result = null;
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset($proCur);
        //允许重试5次
        if ($result === null && $retry < 5) {
            return self::randomDraw($proArr, $is_status, $retry + 1);
        }

        return $result;
    }

}