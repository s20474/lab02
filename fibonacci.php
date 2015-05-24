<?php

class Fibonacci{

    private static $fibHistory = [ 0, 1, 1 ];

    public static function recursive($num){
        if($num == 0){
            return 0;
        }else if($num == 1 || $num == 2){
            return 1;
        }else{
            return self::recursive($num - 1) + self::recursive($num - 2);
        }
    }

    public static function iterative($num){

        $prevPrev = 0;
        $prev = 1;
        $result = 0;

        if($num == 0){
            return 0;
        }else if($num == 1) {
            return 1;
        }

        for($i = 2; $i<=$num; $i++){
            $result = $prev + $prevPrev;
            $prevPrev = $prev;
            $prev = $result;
        }
        return $result;
    }

    public static function optimized($num){
        if($num == 0){
            return self::$fibHistory[$num];
        }else if($num == 1 || $num == 2){
            return self::$fibHistory[$num];
        }else if(array_key_exists($num, self::$fibHistory)){
            return self::$fibHistory[$num];
        }else{
            $temp = self::optimized($num - 1) + self::optimized($num - 2);
            self::$fibHistory[$num] = $temp;
            return $temp;
        }
    }
}
