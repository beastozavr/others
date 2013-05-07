<style>
  .customSelect {
		width: 200px;
	
	}
	
	.customSelect .value{
		padding: 3px;
		background: red;
		float:left;
		border-radius:  5px 0 0 5px;
		width: 165px;
		text-align: center;
		border: 1px solid black;
	}
	
	.customSelect .clicker {
		padding: 3px;
		width: 20px;
		height: 20px;
		float:right;
		background: green;
		border-radius: 0 5px 5px 0;
		cursor: pointer;
		border: 1px solid black;
		border-left: 0px;
	}
	
	.customSelect ul {
		display:none;
		list-style-type: none;
		padding: 3px;
		border: 1px solid black;
		clear:both;
	}
	
	.customSelect ul.opened {
		display:block;
	}
	
	.customSelect ul li {
		cursor: pointer;
	}
	
	.customSelect ul li:hover {
		color: red;
		
	}
</style>
<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<script>
	function customize(el) {
		
		el.after("<div></div>");
		var outerDiv = el.next("div");
		if(el.attr("id")) {
				outerDiv.attr("id", el.attr("id"));
		}
		if(el.attr("class")) {
				outerDiv.attr("class", el.attr("class"));
		}
		outerDiv.addClass("customSelect");
		outerDiv.append("<div class='value'></div>");
		var divVal = outerDiv.children("div.value");
		divVal.html(el.children("option:selected").html());
		outerDiv.append("<div class='clicker'></div>");
		var clicker = outerDiv.children("div.clicker");
		outerDiv.append("<ul></ul>");
		outerDiv.append("<input type='hidden'>");	
		if(el.attr("name")) {
				outerDiv.children("input[type=hidden]").attr("name", el.attr("name"));
		}
		var list = outerDiv.children("ul");
		var selVal = outerDiv.children("input[type=hidden]");
		el.children("option").each(function() {
			var val = $(this).val();
			var html = $(this).html();
			list.append("<li><div>"+html+"</div><input type='hidden' value='"+val+"'></li>");
		});
			
		list.children("li").click(function() {
			selVal.val($(this).children("input[type=hidden]").val());
			divVal.html($(this).children("div").html());
			list.removeClass("opened");
		});
			
		clicker.click(function() {
			if(list.hasClass("opened")) {
				list.removeClass("opened");
			}
			else {
				list.addClass("opened");
			}
		});
		
		el.remove();
			
	}
	
	$(document).ready(function() {
		customize($("select.aa"));
		customize($("select.bb"));
	});
</script>

<select class='custom aa' name="test">
	<option value="0">filter name</option> 
	<option value="1">a1</option> 
	<option value="2">a2</option> 
	<option value="3">a3</option> 
	<option value="4">a4</option> 
</select>

<select class='custom bb' name="test-11">
	<option value="1">1</option> 
	<option value="2" selected>2</option> 
	<option value="3">3</option> 
	<option value="4">4</option> 
</select>
