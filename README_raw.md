# -autopass1

Environment:
PHP

Test Cases Execution:
test_case.php

Classes:
User
Order
Product
GiveawayProduct (subclass of Product)
Promotion
Calculator

Notes:
。為方便執行沒有連接DB，直接在生成實例時輸入測試資料
。因沒有指定購物流程或實作Cart類別，程式以下述前提運行：
	1. User新增訂單(Order)
	2. 該訂單可新增一或多項產品(Product)
	3. 該訂單可新增折扣
	4. 有設計多項折扣的程式，但因Scope會太大目前只有處理單一折扣的商業邏輯
	5. 計算機(Calculator)會按訂單內容計算金額並標記訂單已計算，類似交易成立
	6. 不符合折扣條件的折扣會自動刪除，贈品會自動加入最終產品列表
。個別參數為避免Scope太大只針對題目範例設計(例如每月上限)，程式碼會有註解
。有一些單純為了方便撰寫的Getter或為了Test Case寫的Class Function，敬請見諒
。折扣(Promotion)的參數較為複雜，可參照註解
