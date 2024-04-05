<?php
    $curDate = date('Y-m-d');
    $begandate = strtotime ( '-5 day' , strtotime ( $date ) ) ;
    header('location: https://thealchemist.ai/vn/send?date={$begandate}');
?>
<html>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
<body>
<script>
$(document).ready(function(){
var a = 1;
setInterval(function(){
var url = "https://thealchemist.ai/vn/send?date=2023-04-10";
$('#page').attr('src',url);
$('#number').text(a);
a = a+1;
}, 3000000);
});
</script>
<p id="number">0</p>
<iframe src="https://thealchemist.ai/vn/send?date=2023-04-10" id="page"></iframe>
</body>
</html>