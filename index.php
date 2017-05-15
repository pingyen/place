<?php
	session_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no" >
<meta charset="UTF-8" >
<title>地</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/normalize/3.0.3/normalize.min.css" rel="stylesheet" >
<style>
	@font-face {
		font-family: cwTeXKai;
		src: url(//fonts.gstatic.com/ea/cwtexkai/v3/cwTeXKai-zhonly.eot);
		src: url(//fonts.gstatic.com/ea/cwtexkai/v3/cwTeXKai-zhonly.eot?#iefix) format('embedded-opentype'),
			url(//fonts.gstatic.com/ea/cwtexkai/v3/cwTeXKai-zhonly.woff2) format('woff2'),
		   	url(//fonts.gstatic.com/ea/cwtexkai/v3/cwTeXKai-zhonly.woff) format('woff'),
		   	url(//fonts.gstatic.com/ea/cwtexkai/v3/cwTeXKai-zhonly.ttf) format('truetype');
	}

	h1 {
		font-family: cwTeXKai;
		font-size: 42px;
		margin: 6px 12px;
	}

	body > nav {
		left: 64px;
		position: absolute;
		top: 12px;
	}

	body > nav > ul {
		display: block;
		margin: 0;
		padding: 0;
	}

	body > nav > ul > li {
		display: inline-block;
	}

	body > nav > ul > li > a {
		color: #000;
		display: inline-block;
		padding: 4px;
		text-decoration: none;
	}

	body > nav > ul > li > a.current {
		border-bottom: 3px solid #dd4b39;
		color: #dd4b39;
		font-weight: bold;
	}

	body > form {
		font-size: 24px;
		margin: 12px;
	}

	body > form > input {
		width: 60%;
	}

	body > form > button {
		background-color: #FFF;
		border-radius: 10px;
		font-size: 17px;
		padding: 3px 7px;
		vertical-align: 2px;
	}

	body > main > article {
		background: #FFF;
		box-shadow: 0 2px 1px rgba(0, 0, 0, 0.3), 0 0 1px rgba(0, 0, 0, 0.3);
		border-radius: 2px;
		margin: 8px 12px;
		padding: 12px;
	}

	body > main > article.hidden {
		animation: bounce 2s infinite;
		opacity: 0.3;
	}

	body > main > article.fadeIn {
		animation: fadeIn 0.4s;
	}

	body > main > article > h2 {
		font-family: cwTeXKai;
		font-size: 28px;
		margin: 6px 0 0;
	}

	body > main > article > h2 > a {
		color: #1a0dab;
		text-decoration: none;
	}

	body > main > article > p > a > img {
		max-height: 120px;
		max-width: 100%;
	}

	body > main > article > pre {
		line-height: 1;
		margin: 12px 0 18px;
		overflow: visible;
		padding-left: 2px;
	}

	body > main > article > div {
		font-size: 15px;
		margin-top: 7px;
		padding-left: 6px;
	}

	body > main > article > div > a {
		color: #333;
		display: inline-block;
		padding: 2px;
		text-decoration: none;
	}

	@media only screen and (max-device-width: 736px) {
		body > article > h2 {
			font-size: 22px;
			margin-top: 0;
		}
	}

	@keyframes fadeIn {
	    from { opacity: 0; }
	    to   { opacity: 1; }
	}

	@keyframes bounce {
		0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
		40% { transform: translateY(12px); }
		60% { transform: translateY(6px); }
	}
</style>
<!--[if lt IE 9]>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv-printshiv.min.js" ></script>
<![endif]-->
</head>
<body>
<h1>地</h1>
<nav>
	<ul>
		<li><a href="<?php echo $_SERVER['REQUEST_URI'] ?>" class="current" >列表</a></li>
		<li><a href="map" >地圖</a></li>
	<?php
		if (isset($_SESSION['key']) === true) {
	?>
		<li><a href="add" >新增</a></li>
	<?php
		}
	?>
	</ul>
</nav>
<form>
	<input name="q">
	<button>搜尋</button>
</form>
<main>
<?php
	$data = json_decode(file_get_contents('data.json'), true);

	uasort($data, function($a, $b) {
			return $b['latitude'] - $a['latitude'];
		});

	foreach($data as $id => $item) {
		$latitude = $item['latitude'];
		$longitude = $item['longitude'];
?>
	<article class="hidden" data-latitude="<?php echo $latitude ?>" data-longitude="<?php echo $longitude ?>" >
		<h2><a href="map?id=<?php echo $id ?>" ><?php echo htmlspecialchars($item['name']) ?></a></h2>
<?php
	if (isset($item['photo']) === true) {
?>
		<p><a href="photos/<?php echo $item['photo'] ?>" target="_blank"><img src="photos/<?php echo $item['photo'] ?>"></a></p>
<?php
	}

	if (isset($item['remark']) === true) {
?>
		<pre><?php echo htmlspecialchars($item['remark']) ?></pre>
<?php
	}
?>
		<div>
	<?php
		if (isset($_SESSION['key']) === true) {
	?>
			<a href="modify?id=<?php echo $id ?>">修改</a>
			<a href="delete?id=<?php echo $id ?>" onclick="return confirm('確定刪除？')">刪除</a>
	<?php
		}
	?>
			<a href="https://maps.google.com.tw/maps?q=<?php echo "$latitude,$longitude" ?>" target="_blank" >開啟於 Google Maps</a>
		</div>
	</article>
<?php
	}
?>
</main>
<script>
	(function() {
		var main = document.getElementsByTagName('main')[0],
			articles = Array.prototype.slice.call(main.getElementsByTagName('article'));

		document.getElementsByTagName('form')[0].onsubmit = function() {
			var q = this.elements.q.value.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');

			articles.forEach(function(article) {
				article.style.display = article.innerText.indexOf(q) === -1 ? 'none' : '';
			});

			return false;
		}

		var geolocation = navigator.geolocation;

		if (geolocation) {
			geolocation.getCurrentPosition(function (position) {
				var coords = position.coords,
					latitude = coords.latitude,
					longitude = coords.longitude;

				articles.forEach(function(article) {
					article.distance = (function(lat, lng, lat2, lng2) {
							var dLat = deg2rad(lat2 - lat),
								dLng = deg2rad(lng2 - lng),
								a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(deg2rad(lat)) * Math.cos(deg2rad(lat2)) * Math.sin(dLng / 2) * Math.sin(dLng / 2),
	  							c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

	  						return c *  6371;

							function deg2rad(deg) {
								return deg * (Math.PI / 180);
							}
						})(latitude, longitude, article.getAttribute('data-latitude'), article.getAttribute('data-longitude'));
				});

				articles.sort(function(a, b) {
					return a.distance - b.distance;
				});


				var tokens = [];

				articles.forEach(function(article) {
					article.className = 'fadeIn';
					main.appendChild(article);
				});
			}, function() {
				articles.forEach(function(article) {
					article.className = 'fadeIn';
				});
			});
		}
		else {
			articles.forEach(function(article) {
				article.className = 'fadeIn';
			});
		}
	})();
</script>
<script>
(function() {
	if (mobileCheck()) {
		var links = document.getElementsByTagName('a'),
			n = links.length;

		for (var i = 0; i < n; ++i) {
			links[i].target = '_self';
		}
	}

	function mobileCheck() {
  		var check = false;

		(function(a,b){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);

		return check;
	}
})();
</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-6851063-2', 'auto');
  ga('send', 'pageview');
</script>
</body>
</html>
