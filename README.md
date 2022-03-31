<!DOCTYPE html> 
<html lang="zh">
   <head>
      <meta charset="utf-8"/>
      <link rel="shortcut icon" href="https://www.mdeditor.com/images/logos/favicon.ico" type="image/x-icon"/>
   </head>
   <body>
      <h1 id="h1--autopass1"><a name="-autopass1" class="reference-link"></a><span class="header-link octicon octicon-link"></span>-autopass1</h1>
      <p>Environment:<br>PHP </p>
      <p>Test Cases Execution:<br>test_case.php </p>
      <p>Classes:<br>User<br>Order<br>Product<br>GiveawayProduct (subclass of Product)<br>Promotion<br>Calculator </p>
      <p>Notes:<br>。為方便執行沒有連接DB，直接在生成實例時輸入測試資料<br>。因沒有指定購物流程或實作Cart類別，程式以下述前提運行： </p>
      <ol class="linenums">
         <li class="L0"><code><span class="typ">User</span><span class="pun">新增訂單(</span><span class="typ">Order</span><span class="pun">)</span></code></li>
         <li class="L1"><code><span class="pun">該訂單可新增一或多項產品(</span><span class="typ">Product</span><span class="pun">)</span></code></li>
         <li class="L2"><code><span class="pun">該訂單可新增折扣</span></code></li>
         <li class="L3"><code><span class="pun">有設計多項折扣的程式，但因</span><span class="typ">Scope</span><span class="pun">會太大目前只有處理單一折扣的商業邏輯</span></code></li>
         <li class="L4"><code><span class="pun">計算機(</span><span class="typ">Calculator</span><span class="pun">)會按訂單內容計算金額並標記訂單已計算，類似交易成立</span></code></li>
         <li class="L5"><code><span class="pun">不符合折扣條件的折扣會自動刪除，贈品會自動加入最終產品列表</span></code></li>
      </ol>
      <p>。個別參數為避免Scope太大只針對題目範例設計(例如每月上限)，程式碼會有註解<br>。有一些單純為了方便撰寫的Getter或為了Test Case寫的Class Function，敬請見諒<br>。折扣(Promotion)的參數較為複雜，可參照註解 </p>
   </body>
</html>
