<?php
class RateLimiter
{
    public static function check($action, $limit = 5, $period = 300)
    {
        if (!isset($_SESSION['rate_limit'][$action])) {
            $_SESSION['rate_limit'][$action] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }

        $data = &$_SESSION['rate_limit'][$action];

        // Reset if period passed
        if (time() - $data['first_attempt'] > $period) {
            $data['attempts'] = 0;
            $data['first_attempt'] = time();
        }

        if ($data['attempts'] >= $limit) {
            return false;
        }

        $data['attempts']++;
        return true;
    }

    public static function reset($action)
    {
        unset($_SESSION['rate_limit'][$action]);
    }
}
?>