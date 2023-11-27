<?php
    require_once 'rest_api.php';

    # [키워드 추출]
    function keyword_data()
    {
        # [키워드 파일 읽기]
        $path = "estimate.csv";
        $fp = fopen($path, "r");
        
        # [키워드 데이터]
        $keyword = array();
        
        # [파일 읽기 시작]
        while (($line = fgetcsv($fp)) !== false)
        {
            foreach($line as $field)
            {
                if($field !== "")
                {
                    array_push($keyword, $field);
                }
            }
        }

        fclose($fp);
        return $keyword;
    }


    # [입찰가 추출 함수]
    function keyword_bid($api, $device, $keyword, $rank)
    {
        $req_avg_pos = array("device" => $device, "items" => array(array("key" => $keyword, "position" => $rank)));
        $response = $api->POST('/estimate/average-position-bid/keyword', $req_avg_pos);
        $row_data = array(date('Y-m-d'), $response['estimate'][0]['keyword'], $response['device'], $response['estimate'][0]['position'], $response['estimate'][0]['bid']);
        return $row_data;
    }


    function csv($data)
    {
        # [CSV로 저장]
        $file_path_in_server = "estimate_result.csv";
        $file = fopen($file_path_in_server, 'w'. 'euc-kr');
        fwrite($file, "\xEF\xBB\xBF");

        foreach($data as $row)
        {
            fputcsv($file, $row);
        }

        fclose($file);
    }


    # [메인함수]
    function main()
    {
        # [기본정보]
        $config = parse_ini_file("config.ini");
        $BASE_URL = $config['BASE_URL'];
        $CUSTOMER_ID = $config['CUSTOMER_ID'];
        $API_KEY = $config['API_KEY'];
        $SECRET_KEY = $config['SECRET_KEY'];

        # [API 객체 생성]
        $api = new RestApi($BASE_URL, $API_KEY, $SECRET_KEY, $CUSTOMER_ID);

        $keyword = keyword_data();

        $data = array(array("날짜", "키워드", "기기", "순위", "예상입찰가"));

        
        for($i = 0; $i < count($keyword); $i++)
        {
            # [PC]
            for($j = 1; $j <= 10; $j++)
            {
                $temp_data = keyword_bid($api, "PC", $keyword[$i], $j);
                array_push($data, $temp_data);
            }

            # [Mobile]
            for($j = 1; $j <= 5; $j++)
            {
                $temp_data = keyword_bid($api, "Mobile", $keyword[$i], $j);
                array_push($data, $temp_data);
            }
        }

        csv($data);
    }


    main();
?>