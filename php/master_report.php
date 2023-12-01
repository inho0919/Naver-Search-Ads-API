<?php
    # Full -> 전체
    # Delta -> 일부만  
    ini_set("default_socket_timeout", 30);
    require_once 'rest_api.php';

    $config = parse_ini_file("config.ini");
    $api = new RestApi($config['BASE_URL'], $config['API_KEY'], $config['SECRET_KEY'], $config['CUSTOMER_ID']);
    
    # [Parameter 종류] -> item에 들어갈만한거
    # 1. Account
    # 2. Campaign
    # 3. CampaignBudget
    # 4. BusinessChannel
    # 5. Adgroup
    # 6. AdgroupBudget
    # 7. Keyword
    # 8. Ad
    # 9. AdExtension
    # 10. Qi
    # 11. Label
    # 12. LabelRef
    # 13. Media
    # 14. Biz
    # 15. SeasonalEvent
    # 16. ShoppingProduct
    # 17. ContentsAd
    # 18. PlaceAd
    # 19. CatalogAd
    # 20. AdQi
    # 21. ProductGroup
    # 22. ProductGroupRel
    # 23. Criterion
    # 24. SharedBudget
    # 25. BrandBannerAd
    # 26. BrandThumbnailAd

    $item = "Account"

    $master_full_req = array(
        "item" => $item
    );

    $response = $api->POST("/master-reports", $master_full_req);
    
    $id = $response["id"];
    $status = $response["status"];

    while ($status == 'REGIST' || $status == 'RUNNING') 
    {
        sleep(5);
        $response = $api->GET("/master-reports/".$id);
        $status = $response["status"];
        echo "check : id = $id, status = " . $status . "\n";
    }

    if($status == 'BUILT')
    {
        $api->DOWNLOAD($response["downloadUrl"], "Naver_".$item."_Master_Report.tsv");
    }
    else if ($status == 'ERROR') 
    {
        echo "failed to build master report\n";
    } 
    else if ($status == 'NONE') 
    {
        echo "master has no data\n";
    }



?>