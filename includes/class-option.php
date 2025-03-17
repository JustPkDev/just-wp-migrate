<?php

class Option
{

    public static function set($key, $val)
    {
        return update_option($key, $val);
    }

    public static function get($key)
    {
        return get_option($key);
    }

    public static function delete($key)
    {
        return delete_option($key);
    }
}
