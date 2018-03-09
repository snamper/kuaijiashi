<?php defined('IN_IA') or exit('Access Denied');?>	<div class="container-fluid footer" role="footer">
		<center><?php  echo ihtml_entity_decode($_W['setting']['copyright']['footer']);?></center>
	</div>
	<div class="toTop">
		<i class="fa fa-angle-up"></i><br/>
	</div>
	<div class="toBottom">
		<i class="fa fa-angle-down"></i><br/>
	</div>
	<script>
		$('.js-clip').each(function(){
			util.clip(this, $(this).attr('data-url'));
		});	
		
		$(function(){
			$(window).scroll(function(){
				($(this).scrollTop() > 0 ) ? $('.toTop').fadeIn() : $('.toTop').fadeOut();
				($('body').height() - $(this).scrollTop() > $(this).height() ) ? $('.toBottom').fadeIn() : $('.toBottom').fadeOut();
			})
			$('.toTop').click (function(){
				$('html,body').animate({scrollTop:0},500);
			})
			$('.toBottom').click (function(){
				$('html,body').animate({scrollTop:$('body').height()},500);
			})
			$('.container-fluid.js-body').css('min-height',$(window).height()-125 + 'px');
		})
	</script>
</body>
</html>
