<?php
	$json = file_get_contents('data.json');

	if (isset($_GET['id']) === true) {
		$map = array();

		foreach(explode(', ', $_GET['id']) as $id) {
			$map[$id] = true;
		}

		$data = array();

		foreach(json_decode($json, true) as $id => $item) {
			if (isset($map[$id]) === true) {
				$data[$id] = $item;
			}
		}

		$json = json_encode($data);
	}

	$request = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no" >
<meta charset="UTF-8" >
<title>地 - 地圖</title>
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

	html, body {
		height: 100%;
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

	#map-canvas {
		height: 64vh;
		height: calc(100% - 64px);
	}

	#map-canvas h2 {
		font-family: cwTeXKai;
		font-size: 24px;
		margin: 6px 0;
	}

	#map-canvas h2 > a {
		color: #000;
		text-decoration: none;
	}

	#map-canvas p > a > img {
		max-height: 120px;
		max-width: 100%;
	}

	#map-canvas pre {
		line-height: 1;
		margin: 12px 0 6px;
		overflow: visible;
		padding-left: 2px;
	}

	#map-canvas aside {
		font-size: 11px;
		padding-left: 2px;
	}

	#map-canvas aside > a {
		color: #333;
		display: inline-block;
		padding: 2px;
		text-decoration: none;
	}
</style>
<!--[if lt IE 9]>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv-printshiv.min.js" ></script>
<![endif]-->
<script src="https://maps.googleapis.com/maps/api/js<?php if ($_SERVER['SERVER_NAME'] !== 'localhost') { echo '?key=AIzaSyCoDq0N1wYtdX_Oien1ZZ-wRhE2tIqHJ4k'; } ?>" ></script>
</head>
<body>
<h1>地</h1>
<nav>
	<ul>
		<li><a href="<?php echo substr($request, 0, strrpos($request, '/')) ?>" >列表</a></li>
		<li><a href="map" class="current" >地圖</a></li>
		<li><a href="add" >新增</a></li>
	</ul>
</nav>
<div id="map-canvas"></div>
<script>
	(function() {
		var escapeHTML = function(input) {
			var map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};

			return input.replace(/[&<>"']/g, function(m) { return map[m]; });
		}

		var maps = google.maps,
			event = maps.event,
			zIndex = 1000,
			map = new maps.Map(document.getElementById('map-canvas')),
			data = <?php echo $json ?>,
			latLngBounds = new maps.LatLngBounds();

		for (var id in data) {
			(function(id, item) {
				var address = item.address,
					latitude = item.latitude,
					longitude = item.longitude,
					latLng = new maps.LatLng(latitude, longitude),
					photo = item.photo,
					remark = item.remark,
					tokens = ['<h2><a href="map?id=' + id + '" >' + escapeHTML(address) + '</a></h2>'];

				if (photo !== undefined) {
					tokens.push('<p><a href="photos/' + photo + '" target="_blank"><img src="photos/' + photo + '"></a></p>');
				}				

				if (remark !== undefined) {
					tokens.push('<pre>' + escapeHTML(remark) + '</pre>');
				}

				tokens.push('<aside>');
				tokens.push('<a href="modify?id=' + id + '">修改</a>')
				tokens.push('<a href="delete?id=' + id + '" onclick="return confirm(\'確定刪除？\')">刪除</a>');
				tokens.push('<a href="https://maps.google.com.tw/maps?q=' + latitude + ',' + longitude + '" target="_blank" >開啟於 Google Maps</a>');
				tokens.push('</aside>');

  				var infowindow = new maps.InfoWindow({
					content: tokens.join(' ')
				});

				var marker = new maps.Marker({
					position: latLng,
					map: map,
					title: address
				});

				event.addListener(marker, 'click', function() {
					infowindow.setZIndex(++zIndex);
					infowindow.open(map, marker);
  				});

				latLngBounds.extend(latLng);
			})(id, data[id]);
		}

		map.setCenter(latLngBounds.getCenter());
		map.fitBounds(latLngBounds);

		var geolocation = navigator.geolocation;

		if (geolocation) {
		    geolocation.getCurrentPosition(function (position) {
				var coords = position.coords,
					latLng = new maps.LatLng(coords.latitude, coords.longitude),
					infowindow = new maps.InfoWindow({
					    content: '目前位置'
					}),
					marker = new maps.Marker({
						position: latLng,
					    map: map,
						title: '目前位置',
						icon: {
							anchor: new maps.Point(15, 16),
							path: 'M 15.625,0.625 19.375,11.25 30.625,11.25 21.875,18.125 25,28.75 15.625,22.5 6.25,28.75 9.375,18.125 0.375,11.25 11.875,11.25 z',
							fillColor: 'yellow',
							fillOpacity: 0.8,
							strokeColor: 'gold',
							strokeWeight: 2.4
						},
						zIndex: 10000
				    });

				event.addListener(marker, 'click', function() {
					infowindow.open(map, marker);
				});

				map.setCenter(latLng);
				latLngBounds.extend(latLng);
				map.fitBounds(latLngBounds);
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
