<?php 
	// 載入Class
	$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("PromotionCalculator"), RecursiveIteratorIterator::SELF_FIRST);
	foreach($objects as $filename){
		if (substr($filename, -4)==".php")
			require $filename;
	}

	use PromotionCalculator\User;
	use PromotionCalculator\Product;
	use PromotionCalculator\Products\GiveawayProduct;
	use PromotionCalculator\Promotion;
	use PromotionCalculator\Order;
	use PromotionCalculator\Calculator;

	// 美化在HTML的輸出
	function formatOutput($source){
		echo "<pre>".print_r($source, 1)."</pre>";
	}

	// 輸出Array
	function arrayOutput($source){
		echo "<pre>";
		foreach ($source as $key => $value) {
			echo "{$key}: {$value}\n";
		}
		echo "</pre>";
	}

	/*************************************/
	/*** Test Instances Build Start ***/

	/*** User實例 Start ***/
	$user1 = new User(1);
	$user2 = new User(2);
	$user3 = new User(3);
	/*** User實例 End ***/

	/*** 產品實例 Start ***/
	$product1 = new Product(1, ['name'=>"Product 1", 'code'=>"prd001"], 300, 200);	// 原價300，實價200
	$product2 = new Product(2, ['name'=>"Product 2", 'code'=>"prd002"], 500);		// 原價500
	$product3 = new Product(3, ['name'=>"Product 3", 'code'=>"prd003"], 100, 80);	// 原價100，實價80
	$product4 = new Product(4, ['name'=>"Product 4", 'code'=>"prd004"], 150);		// 實價150

	// 因為沒有實作DB，贈品要手動生成實體，實際系統應該會在DB讀同一筆產品資料，再根據贈品設定更改需要的欄位(如有)
	$giveaway1 = new GiveawayProduct(1, ['name'=>"Product 1", 'code'=>"prd001"]); // 跟$product1是同一個
	$giveaway2 = new GiveawayProduct(5, ['name'=>"Product 5(Giveaway Only)", 'code'=>"prdga005"]); // 不在Product實例
	/*** 產品實例 End ***/

	/*** 折扣實例 Start ***/
	// 訂單滿1000折100元
	$buy1000reduce100 = new Promotion(1, [
		'threshold_type'	=> "amount",
		'threshold_value'	=> 1000,
		// 'threshold_target'	=> ,
		'promote_type'		=> "amount",
		'promote_value'		=> 100,
		// 'limit_type'		=> ,
		// 'limit_unit'		=> ,
		// 'limit_value'		=> ,
	]);

	// 訂單滿1000折10%
	$buy1000reduce10P = new Promotion(2, [
		'threshold_type'	=> "amount",
		'threshold_value'	=> 1000,
		// 'threshold_target'	=> ,
		'promote_type'		=> "percentage",
		'promote_value'		=> 10,
		// 'limit_type'		=> ,
		// 'limit_unit'		=> ,
		// 'limit_value'		=> ,
	]);

	// 商品(ID:1)滿3件折30元
	$buyPrd1_3reduce30 = new Promotion(3, [
		'threshold_type'	=> "quantity",
		'threshold_value'	=> 3,
		'threshold_target'	=> 1,
		'promote_type'		=> "amount",
		'promote_value'		=> 30,
		// 'limit_type'		=> ,
		// 'limit_unit'		=> ,
		// 'limit_value'		=> ,
	]);

	// 訂單滿1000送特定商品(ID:1)
	$buy1000giveawayPrd1 = new Promotion(4, [
		'threshold_type'	=> "amount",
		'threshold_value'	=> 1000,
		// 'threshold_target'	=> 1,
		'promote_type'		=> "giveaway",
		'promote_value'		=> $giveaway1,
		// 'limit_type'		=> ,
		// 'limit_unit'		=> ,
		// 'limit_value'		=> ,
	]);

	// 訂單滿1000送100元，折扣總共只能用2次
	$buy1000reduce100_limit2T_self = new Promotion(5, [
		'threshold_type'	=> "amount",
		'threshold_value'	=> 1000,
		// 'threshold_target'	=> 1,
		'promote_type'		=> "amount",
		'promote_value'		=> 100,
		'limit_type'		=> 'times',
		'limit_unit'		=> 'self',
		'limit_value'		=> 2,
	]);

	// 訂單滿1000折10%，折扣每人只能總共優惠300元
	$buy1000reduce10P_limit300_user = new Promotion(6, [
		'threshold_type'	=> "amount",
		'threshold_value'	=> 1000,
		// 'threshold_target'	=> 1,
		'promote_type'		=> "percentage",
		'promote_value'		=> 10,
		'limit_type'		=> 'amount',
		'limit_unit'		=> 'user',
		'limit_value'		=> 300,
	]);

	// 訂單滿1000折100元，折扣每月上限為200元
	$buy1000reduce10P_limit200_month = new Promotion(6, [
		'threshold_type'	=> "amount",
		'threshold_value'	=> 1000,
		// 'threshold_target'	=> 1,
		'promote_type'		=> "amount",
		'promote_value'		=> 100,
		'limit_type'		=> 'amount',
		'limit_unit'		=> 'month',
		'limit_value'		=> 200,
	]);
	/*** 折扣實例 End ***/

	/*** Test Instances Build End ***/
	/*************************************/


	/*************************************/
	/*** 測試Start ***/

	// 前四項折扣測試(沒有上限條件)會使用$user1多次新增訂單
	// 其餘3項會由$user2開始
	// 註：由於沒有購物流程，本設計假設訂單在通過計算機後視為結帳(折扣確定、納入上限計算...等)

	// Test case 1 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 1:");
	formatOutput("折扣: 訂單滿1000折100(\$buy1000reduce100)");
	formatOutput("購買產品: [Product 1]*1 [Product 2]*1");
	formatOutput("<b><u>預期訂單原價: 700</u></b> (200*1+500*1)");
	formatOutput("<b><u>預期折扣金額: 0</u></b> (未達滿額要求，不觸發，折扣條件每種僅測一次)");
	formatOutput("<b><u>預期實付金額: 700</u></b> (700-0)");
	$user = $user1;
	$order = $user->newOrder(1);
	$order->addProduct($product1)->addProduct($product2)->addPromotion($buy1000reduce100);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 1 End

	// Test case 2 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 2:");
	formatOutput("折扣: 訂單滿1000折100(\$buy1000reduce100)");
	formatOutput("購買產品: [Product 1]*2 [Product 2]*2");
	formatOutput("<b><u>預期訂單原價: 1400</u></b> (200*2+500*2)");
	formatOutput("<b><u>預期折扣金額: 100</u></b> (滿1000折100)");
	formatOutput("<b><u>預期實付金額: 1300</u></b> (1400-100)");
	$user = $user1;
	$order = $user->newOrder(2);
	$order->addProduct($product1, 2)->addProduct($product2, 2)->addPromotion($buy1000reduce100);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 2 End

	// Test case 3 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 3:");
	formatOutput("折扣: 訂單滿1000折10%(\$buy1000reduce10P)");
	formatOutput("購買產品: [Product 3]*10 [Product 4]*2");
	formatOutput("<b><u>預期訂單原價: 1100</u></b> (80*10+150*2)");
	formatOutput("<b><u>預期折扣金額: 110</u></b> (110*10%)");
	formatOutput("<b><u>預期實付金額: 990</u></b> (1100-110)");
	$user = $user1;
	$order = $user->newOrder(3);
	$order->addProduct($product3, 10)->addProduct($product4, 2)->addPromotion($buy1000reduce10P);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 3 End

	// Test case 4 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 4:");
	formatOutput("折扣: 買[Product 1]3件折30元(\$buyPrd1_3reduce30)");
	formatOutput("購買產品: [Product 1]*2 [Product 2]*5");
	formatOutput("<b><u>預期訂單原價: 2900</u></b> (200*2+500*5)");
	formatOutput("<b><u>預期折扣金額: 0</u></b> (未達滿額要求，不觸發，折扣條件每種僅測一次)");
	formatOutput("<b><u>預期實付金額: 2900</u></b> (2900-0)");
	$user = $user1;
	$order = $user->newOrder(4);
	$order->addProduct($product1, 2)->addProduct($product2, 5)->addPromotion($buyPrd1_3reduce30);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 4 End

	// Test case 5 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 5:");
	formatOutput("折扣: 買[Product 1]3件折30元(\$buyPrd1_3reduce30)");
	formatOutput("購買產品: [Product 1]*4 [Product 2]*5");
	formatOutput("<b><u>預期訂單原價: 3300</u></b> (200*4+500*5)");
	formatOutput("<b><u>預期折扣金額: 30</u></b> (Product 1滿3件折30)");
	formatOutput("<b><u>預期實付金額: 3270</u></b> (3300-30)");
	$user = $user1;
	$order = $user->newOrder(5);
	$order->addProduct($product1, 4)->addProduct($product2, 5)->addPromotion($buyPrd1_3reduce30);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 5 End

	// Test case 6 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 6:");
	formatOutput("折扣: 訂單滿1000送特定商品(\$buy1000giveawayPrd1)");
	formatOutput("購買產品: [Product 1]*3 [Product 2]*1");
	formatOutput("<b><u>預期訂單原價: 1100</u></b> (200*3+500*1)");
	formatOutput("<b><u>預期折扣金額: 0</u></b> (優惠為贈品，顯示於產品列表)");
	formatOutput("<b><u>預期實付金額: 1100</u></b> (1100-0)");
	$user = $user1;
	$order = $user->newOrder(6);
	$order->addProduct($product1, 3)->addProduct($product2, 1)->addPromotion($buy1000giveawayPrd1);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 6 End

	// Test case 7 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 7:");
	formatOutput("折扣: 訂單滿1000送100元，折扣總共只能用2次<b>[第一次使用(user2)]</b>(\$buy1000reduce100_limit2T_self)");
	formatOutput("購買產品: [Product 1]*3 [Product 2]*1");
	formatOutput("<b><u>預期訂單原價: 1100</u></b> (200*3+500*1)");
	formatOutput("<b><u>預期折扣金額: 100</u></b> (滿1000折100)");
	formatOutput("<b><u>預期實付金額: 1000</u></b> (1100-100)");
	$user = $user2;
	$order = $user->newOrder(7);
	$order->addProduct($product1, 3)->addProduct($product2, 1)->addPromotion($buy1000reduce100_limit2T_self);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 7 End

	// Test case 8 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 8:");
	formatOutput("折扣: 訂單滿1000送100元，折扣總共只能用2次<b>[嘗試第二次使用但未達折扣條件]</b>(\$buy1000reduce100_limit2T_self)");
	formatOutput("購買產品: [Product 1]*1 [Product 2]*1");
	formatOutput("<b><u>預期訂單原價: 700</u></b> (200*1+500*1)");
	formatOutput("<b><u>預期折扣金額: 0</u></b> (未達折扣條件)");
	formatOutput("<b><u>預期實付金額: 700</u></b> (700-0)");
	$user = $user2;
	$order = $user->newOrder(8);
	$order->addProduct($product1, 1)->addProduct($product2, 1)->addPromotion($buy1000reduce100_limit2T_self);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 8 End

	// Test case 9 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 9:");
	formatOutput("折扣: 訂單滿1000送100元，折扣總共只能用2次<b>[第二次使用(user3)]</b>(\$buy1000reduce100_limit2T_self)");
	formatOutput("購買產品: [Product 3]*4 [Product 4]*5");
	formatOutput("<b><u>預期訂單原價: 1070</u></b> (80*4+150*5)");
	formatOutput("<b><u>預期折扣金額: 100</u></b> (滿1000折100)");
	formatOutput("<b><u>預期實付金額: 970</u></b> (1070-100)");
	$user = $user3;
	$order = $user->newOrder(9);
	$order->addProduct($product3, 4)->addProduct($product4, 5)->addPromotion($buy1000reduce100_limit2T_self);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 9 End

	// Test case 10 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 10:");
	formatOutput("折扣: 訂單滿1000送100元，折扣總共只能用2次<b>[嘗試第二次使用(user2)]</b>(\$buy1000reduce100_limit2T_self)");
	formatOutput("購買產品: [Product 1]*5 [Product 2]*2");
	formatOutput("<b><u>預期訂單原價: 2000</u></b> (200*5+500*2)");
	formatOutput("<b><u>預期折扣金額: 0</u></b> (user2及user3先前於Test case 7/9已各成功使用一次，已達折扣使用上限)");
	formatOutput("<b><u>預期實付金額: 2000</u></b> (2000-0)");
	$user = $user2;
	$order = $user->newOrder(10);
	$order->addProduct($product1, 5)->addProduct($product2, 2)->addPromotion($buy1000reduce100_limit2T_self);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 10 End

	// Test case 11 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 11:");
	formatOutput("折扣: 訂單滿1000折10%，折扣每人只能總共優惠300元<b>[user2折110元]</b>(\$buy1000reduce10P_limit300_user)");
	formatOutput("購買產品: [Product 1]*3 [Product 2]*1");
	formatOutput("<b><u>預期訂單原價: 1100</u></b> (200*1+500*1)");
	formatOutput("<b><u>預期折扣金額: 110</u></b> (訂單滿1000折10%)");
	formatOutput("<b><u>預期實付金額: 990</u></b> (1100-110)");
	$user = $user2;
	$order = $user->newOrder(11);
	$order->addProduct($product1, 3)->addProduct($product2, 1)->addPromotion($buy1000reduce10P_limit300_user);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 11 End

	// Test case 12 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 12:");
	formatOutput("折扣: 訂單滿1000折10%，折扣每人只能總共優惠300元<b>[user3折200元]</b>(\$buy1000reduce10P_limit300_user)");
	formatOutput("購買產品: [Product 2]*4");
	formatOutput("<b><u>預期訂單原價: 2000</u></b> (500*4)");
	formatOutput("<b><u>預期折扣金額: 200</u></b> (訂單滿1000折10%，Test case 11為user2，此user3為第一次使用折扣)");
	formatOutput("<b><u>預期實付金額: 1800</u></b> (2000-200)");
	$user = $user3;
	$order = $user->newOrder(12);
	$order->addProduct($product2, 4)->addPromotion($buy1000reduce10P_limit300_user);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 12 End

	// Test case 13 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 13:");
	formatOutput("折扣: 訂單滿1000折10%，折扣每人只能總共優惠300元<b>[user2嘗試折220元]</b>(\$buy1000reduce10P_limit300_user)");
	formatOutput("購買產品: [Product 1]*1 [Product 2]*4");
	formatOutput("<b><u>預期訂單原價: 2200</u></b> (200*1+500*4)");
	formatOutput("<b><u>預期折扣金額: 190</u></b> (折10%原為220，但user2已於Test case 11使用110額度，剩餘最多折300-100=190)");
	formatOutput("<b><u>預期實付金額: 2010</u></b> (2200-190)");
	$user = $user2;
	$order = $user->newOrder(13);
	$order->addProduct($product1, 1)->addProduct($product2, 4)->addPromotion($buy1000reduce10P_limit300_user);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 13 End

	// Test case 14 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 14:");
	formatOutput("折扣: 訂單滿1000折10%，折扣每人只能總共優惠300元<b>[user2嘗試300元額滿後再次使用折扣]</b>(\$buy1000reduce10P_limit300_user)");
	formatOutput("購買產品: [Product 2]*2 [Product 3]*3");
	formatOutput("<b><u>預期訂單原價: 1240</u></b> (500*2+80*3)");
	formatOutput("<b><u>預期折扣金額: 0</u></b> (user2已於Test case 11/13使用完300額度)");
	formatOutput("<b><u>預期實付金額: 1240</u></b> (1240-0)");
	$user = $user2;
	$order = $user->newOrder(14);
	$order->addProduct($product2, 2)->addProduct($product3, 3)->addPromotion($buy1000reduce10P_limit300_user);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 14 End

	// Test case 14 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 14:");
	formatOutput("折扣: 訂單滿1000折10%，折扣每人只能總共優惠300元<b>[user2嘗試300元額滿後再次使用折扣]</b>(\$buy1000reduce10P_limit300_user)");
	formatOutput("購買產品: [Product 2]*2 [Product 3]*3");
	formatOutput("<b><u>預期訂單原價: 1240</u></b> (500*2+80*3)");
	formatOutput("<b><u>預期折扣金額: 0</u></b> (user2已於Test case 11/13使用完300額度)");
	formatOutput("<b><u>預期實付金額: 1240</u></b> (1240-0)");
	$user = $user2;
	$order = $user->newOrder(14);
	$order->addProduct($product2, 2)->addProduct($product3, 3)->addPromotion($buy1000reduce10P_limit300_user);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 14 End

	// Test case 15 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 15:");
	formatOutput("折扣: 訂單滿1000折100元，折扣(每人)每月上限為200元<b>[user2折100元，本月第一次使用]</b>(\$buy1000reduce10P_limit200_month)");
	formatOutput("購買產品: [Product 1]*5 [Product 2]*2");
	formatOutput("<b><u>預期訂單原價: 2000</u></b> (200*5+500*2)");
	formatOutput("<b><u>預期折扣金額: 100</u></b> (訂單滿1000折100元)");
	formatOutput("<b><u>預期實付金額: 1900</u></b> (2000-100)");
	$user = $user2;
	$order = $user->newOrder(15);
	$order->addProduct($product1, 5)->addProduct($product2, 2)->addPromotion($buy1000reduce10P_limit200_month);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 15 End

	// Test case 16 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 16:");
	formatOutput("折扣: 訂單滿1000折100元，折扣(每人)每月上限為200元<b>[user2折100元，此訂單手動設定為前一個月]</b>(\$buy1000reduce10P_limit200_month)");
	formatOutput("購買產品: [Product 1]*2 [Product 2]*2");
	formatOutput("<b><u>預期訂單原價: 1400</u></b> (200*2+500*2)");
	formatOutput("<b><u>預期折扣金額: 100</u></b> (訂單滿1000折100元)");
	formatOutput("<b><u>預期實付金額: 1300</u></b> (1400-100)");
	$user = $user2;
	$order = $user->newOrder(16);
	$order->addProduct($product1, 2)->addProduct($product2, 2)->addPromotion($buy1000reduce10P_limit200_month);
	$order_result = Calculator::calculateOrder($order, $user);
	$order->overrideDate(date_create("now")->modify("-1 month"));
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 16 End

	// Test case 17 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 17:");
	formatOutput("折扣: 訂單滿1000折100元，折扣(每人)每月上限為200元<b>[user2折100元，本月第二次使用]</b>(\$buy1000reduce10P_limit200_month)");
	formatOutput("購買產品: [Product 2]*1 [Product 4]*5");
	formatOutput("<b><u>預期訂單原價: 1250</u></b> (500*1+150*5)");
	formatOutput("<b><u>預期折扣金額: 100</u></b> (訂單滿1000折100元，Test case 16之訂單為上個月，不併入本月限額計算)");
	formatOutput("<b><u>預期實付金額: 1150</u></b> (1250-100)");
	$user = $user2;
	$order = $user->newOrder(17);
	$order->addProduct($product2, 1)->addProduct($product4, 5)->addPromotion($buy1000reduce10P_limit200_month);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 17 End

	// Test case 18 Start
	formatOutput("----------------------------------------------------------------");
	formatOutput("Test Case 18:");
	formatOutput("折扣: 訂單滿1000折100元，折扣(每人)每月上限為200元<b>[user2折100元，嘗試本月第三次使用]</b>(\$buy1000reduce10P_limit200_month)");
	formatOutput("購買產品: [Product 1]*4 [Product 3]*3");
	formatOutput("<b><u>預期訂單原價: 1040</u></b> (200*4+80*3)");
	formatOutput("<b><u>預期折扣金額: 0</u></b> (Test case 15/17已各折扣100元一次，200元每月上限已用完)");
	formatOutput("<b><u>預期實付金額: 1040</u></b> (1250-0)");
	$user = $user2;
	$order = $user->newOrder(18);
	$order->addProduct($product1, 4)->addProduct($product3, 3)->addPromotion($buy1000reduce10P_limit200_month);
	$order_result = Calculator::calculateOrder($order, $user);
	echo "<b>";
	formatOutput("<h3>輸出結果:</h3>");
	$order->printProducts();
	arrayOutput($order_result);
	formatOutput("----------------------------------------------------------------");
	echo "</b>";
	// Test case 18 End


	// // 訂單滿1000折100元，折扣每月上限為300元
	// $buy1000reduce10P_limit200_month = new Promotion(6, [
	// 	'threshold_type'	=> "amount",
	// 	'threshold_value'	=> 1000,
	// 	// 'threshold_target'	=> 1,
	// 	'promote_type'		=> "amount",
	// 	'promote_value'		=> 100,
	// 	'limit_type'		=> 'amount',
	// 	'limit_unit'		=> 'month',
	// 	'limit_value'		=> 300,
	// ]);
	// /*** 折扣實例 End ***/

	/*** 測試End ***/
	/*************************************/

 ?>