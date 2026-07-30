<?php

namespace M4h45amu7x;

class Voucher
{
    private $mobile;
    private $voucher_code;

    public function __construct($mobile, $voucher_url)
    {
        $this->mobile = $mobile;
        $this->voucher_code = $this->extractVoucherCode($voucher_url);
    }

    private function extractVoucherCode($url)
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        return isset($query['v']) ? $query['v'] : $url;
    }

    public function verify()
    {
        $url = 'https://gift.truemoney.com/campaign/vouchers/' . $this->voucher_code . '/verify?mobile=' . $this->mobile;
        return $this->request($url, 'GET');
    }

    public function redeem()
    {
        $url = 'https://gift.truemoney.com/campaign/vouchers/' . $this->voucher_code . '/redeem';
        $data = json_encode(['mobile' => $this->mobile, 'voucher_hash' => $this->voucher_code]);
        return $this->request($url, 'POST', $data);
    }

    private function request($url, $method = 'GET', $data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
