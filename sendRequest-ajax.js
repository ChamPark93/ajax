/************************************************************************************

sendRequest-ajax Another Branches 버전

author : 허정진  http://serpiko.tistory.com

update : 2017. 11. 08




[ update history ] 

- 2017. 11. 08 추가

  txt 파일 불러올때 빠르게 사용할 수 있도록 예제 추가, onprogress 함수 로깅수정

	var xhr = new XhrClass();
	xhr.sendRequest( $Config['PackageName']+"/files/termsAndConditions.txt", "", function(xhr, res){
		$("#terms_textarea").text( res );
		$('#termsModal').modal('show');
	}, 'text');

	timeout IE10, 11 에서 InvalidStateError 출력되며 런타임에러 : 원인은 The bug is gone (in IE11) if you set the timeout after the open

- 2017. 03. 21 추가

  hide() 함수 추가 => 로딩텍스트 감춥니다.

  사용)
  var xhrClass = new XhrClass();
  xhrClass.hide();

- 2017. 03. 08 변경, 추가

  textDisplayDelay( millisecond ) 함수 추가 => 전역오브젝트인 textMillisecond 를 참조하여 변경합니다.

  textDisplayHandler 함수 수정.

  사용)
  var xhrClass = new XhrClass();
  xhrClass.textDisplayDelay( 500 );


- 2017.01.18 추가

   inherit 상속 객체 추가.

- 2017.01.17 변경
	
	destroy 로직 변경
	
	객체의 containerName 멤버변수에 정의된 이름으로 id를 가진 element있는지 확인 후 removeChild.

	객체의 xhr 멤버변수에 XhrClass가 있는지 확인 후 abort 합니다.
	
- 2017.01.02 추가

	destroy 와 dispose 를 추가.
	
	destroy()를 호출하면 객체의 모든 프로퍼티와 prototype을 제거합니다.
	repeat메서드를 통해 지속적으로 정보를 갱신중인경우, 반드시 사용할 것을 권장합니다.

- 2016.12.26 변경

	param에서 encode가 적용되도록 수정됨

- 2016.12.02 추가	

	textElementCreate(), textDisplayHandler() : 로딩텍스트를 js로 한번만 추가하고 동작합니다.
	get textDisplay, set textDisplay : 로딩 텍스트 제어
	repeat(), stop() : 반복해서 전송 요청(sendRequest)을 사용하거나 반복요청을 중단합니다.
	abort() : 객체의 통신을 직접중단합니다.

- 2016.11.30 변경

	해당 인스턴스 생성시, 메모리 누수를 위해 이벤트 리스너 함수는 클래스 생성시 한번만 등록합니다.


---------------------------------------------------------------------------------------------------------------------


[ 권장하는 사용법 ]


#text혹은 json의 경우

//① Closer
(function(){

	//② 클래스 객체생성 ( object pool을 사용하여 한번만 객체 생성)
	var xhrClass = new XhrClass();
	//xhrClass.hide(); // => loading 텍스트효과를 사용하지 않으려면 설정합니다.
	//xhrClass.textDisplay = false; // => loading 텍스트효과를 사용하지 않으려면 설정합니다.
	//xhrClass.textDisplayDelay( 500 ); // => 로딩 표시 지속시간

	//③ url과 parameter 입력
	var url = "data.php";
	var param = "Act=default";

	//④ class의 sendRequest메서드를 사용하여 XMLHTTPRequest를 호출.
	// 필수 : url, param, callbackFunction,    옵션: [ ,type('json','text'), method('get', 'post'), asyncBool(true, false), log(false, true) ]

	xhrClass.sendRequest(url, param, complete);
	function complete(xhr, res)
	{
		//console.log( res );

		var name = res;			// <= 'text'
		var name = res.val;		// <= 'json'
		var name = res[0].val;	// <= 'json'
	}
	
	//⑤ sendRequest 반복호출
	xhrClass.repeat( 5000 ); //파라메터없으면 기본값은 1000 * 60 (millisecond) 입니다.

	//⑥ sendRequest 반복중지
	xhtClass.stop();

	//⑦ 수동으로 xhr 인스턴스의 데이터 통신을 중지합니다.
	xhrClass.abort();

})();



# file 업로드의 경우

//① Closer
(function(){
	$(document).on("click touchstart", "#button", function(e){
		
		//② 클래스 객체생성 ( object pool을 사용하여 한번만 객체 생성)
		var xhrFileClass = new XhrClass();

		//③ url과 FormData 전송
		var url = "upload.php";
		var Act = $("form").find("input[name='Act']");
		var fileInput = $("form").find("input[name='upload_file']");
		//console.log( fileInput[0].files[0] );
		
		var formData = new FormData();
		formData.append("Act", Act.val() );
		formData.append("upload_file", fileInput[0].files[0]);
		
		//④ class의 sendRequest메서드를 사용하여 XMLHTTPRequest를 호출.
		// 필수 : url, FormData, callbackFunction, type('json','text'), "FILE", 옵션 : [, asyncBool(true, false), log(false, true) ]
		xhrFileClass.sendRequest(url, formData, callback, "text", "FILE");
		function callback(xhr, res){
			console.log( res );
		}
		
	});
})();


************************************************************************************/




var XhrClass = function($obj){
	
	//private
	var name	= "XhrClass";
	var author	= "허정진";
	var ver		= "2017.03.10";
	
	//public
	this.name = name;
	this.xhr = null;
	this.intervalID = null;
	this.timeoutID = null;
	this.containerName = "sendRequest-ajax_textDisplay_container";

	//sendRequest메서드에서 사용할 전역변수
	this.url;
	this.param = "";
	this.callback;

	//external scalable extend : 인스턴스 생성시 외부에서 확장 가능한 객체
	this.defaultObj = {
		"type"		: "json",
		"method"	: "GET",
		"asyncBool" : true,
		"log"		: false,
		"timeout"	: 1000 * 29,
		"millisecond" : 1000 * 60,
		"textMillisecond" : 500,
		"textDisplay" : true
	};
	for(var key in $obj){
		if( $obj.hasOwnProperty(key) ){
			this.defaultObj[key] = $obj[key];
		}
	}
	
	//build
	this.build.classLength++;
	this.build();

}//end. XhrClass

XhrClass.prototype = (function(){

	return{
		
		/**************************************************************************************************
		*
		* @ build : 생성자
		*
		**************************************************************************************************/
		build: function(){

			/************************************************************************************
			*
			* String.prototype: Trim, Ltrim, Rtrim
			*
			************************************************************************************/
			String.prototype.Trim = function() 
			{
				return this.replace(/(^\s*)|(\s*$)/gi, "");
			};

			String.prototype.Ltrim = function() 
			{
				return this.replace(/^\s*/, "");
			};

			String.prototype.Rtrim = function() 
			{
				return this.replace(/\s*$/, "");
			};
			
			this.init();
		},

		/******************************************************************************************************************
		*
		*	inherit : 상속 함수
		*
		******************************************************************************************************************/
		inherit: function( Parent, Child ){
			Child = function(){
				Parent.call( this );
			}
			
			try{
				if (!Object.create) {
					Object.create = (function(){
						function F(){}
							return function(o){
							if (arguments.length != 1) {
								throw new Error('Object.create implementation only accepts one parameter.');
							}
							F.prototype = o;
							return new F();
						}
					})();
				}
				
				Child.prototype = Object.create( Parent.prototype );
				
				Child.prototype.constructor = Child;
				
				//override
				//Child.prototype.build = function(){ alert('hi, I am a Child'); };
				
				var child = new Child();
				if(child instanceof Parent === true) 
					return child;
				else 
					return new Parent();

			}catch(e){
				throw new Error( "[ inherit Error ] : "+ Parent.name +"객체를 상속받지 못했습니다. "+ Parent.name +" 객체를 '확인'해 주세요." );
			}
		},
		
		/**************************************************************************************************
		*
		* @ textElementCreate : 메세지 띄울 엘리먼트 생성한다
		*
		**************************************************************************************************/
		textElementCreate: function(){
			
			// check ID length
			if( document.getElementById( this.containerName ) != null ) return;
			
			// div
			var div = document.createElement("div");
			var att = document.createAttribute("id"); 
			att.value = this.containerName;
			div.setAttributeNode(att);
			
			// div in i
			var i = document.createElement("i");
			att = document.createAttribute("class");  
			att.value = 'fa fa-refresh fa-spin fa-lg';
			i.setAttributeNode(att);
			div.appendChild(i);

			// div in span
			var span = document.createElement("span");
			att = document.createAttribute("id");  
			att.value = 'sendRequest-ajax_directionText';
			span.setAttributeNode(att);
			span.textContent = "\u00A0\u00A0서버와 통신 중 입니다..."; // \u00A0 = &nbsp; (unicode encode)
			div.appendChild(span);

			//div.innerHTML = "<i class='fa fa-refresh fa-spin fa-lg'></i>&nbsp;&nbsp;<span id='sendRequest-ajax_directionText'>서버와 통신 중 입니다...</span>";

			var body = document.body || document.getElementsByTagName('body')[0];
			body.insertBefore(div, body.childNodes[0]); // body.firstChild
			
			//style
			var container = document.getElementById( this.containerName );
			container.style.width = "100%";
			container.style.height = "100%";
			container.style.color = "#FFF";
			container.style.fontSize = "17px";
			container.style.fontWeight = "bold";
			container.style.position = "absolute";
			container.style.left = 0;
			container.style.top = 0;
			container.style.backgroundColor = "rgba(0,0,0, 0.35)";
			container.style.textAlign = "center";
			container.style.zIndex = 8000;
			container.style.paddingTop = "70px";
			container.style.display = "none";
		},
		
		textDisplayDelay: function($textMillisecond){

			if( typeof $textMillisecond == "undefined" ) return;

			this.defaultObj.textMillisecond = $textMillisecond;
		},
		
		/**************************************************************************************************
		*
		* @ textDisplayHandler : 로딩메세지를 띄운다
		*
		**************************************************************************************************/
		textDisplayHandler: function($bool){
			
			var $this = this;
			var sec = $this.defaultObj.textMillisecond;
			
			if( $this.defaultObj.textDisplay == false ) return;

			if( $bool == true ){
				document.getElementById( $this.containerName ).style.display = "block";
			}else{
				$this.timeoutID = window.setTimeout( function(){

					if( $this.timeoutID ) window.clearTimeout( $this.timeoutID );

					document.getElementById( $this.containerName ).style.display = "none";
				}, sec );
			}
		},
		
		/**************************************************************************************************
		*
		* @ textDisplay : (getter / setter) Class.textDisplay 로 로딩효과를 제어합니다
		*
		**************************************************************************************************/
		get textDisplay(){return this.defaultObj.textDisplay;},
		set textDisplay($bool){this.defaultObj.textDisplay = $bool;},
		
		/**************************************************************************************************
		*
		* @ hide() 로딩효과를 보이지 않게 합니다.
		*
		**************************************************************************************************/
		hide: function(){this.defaultObj.textDisplay = false;},

		/**************************************************************************************************
		*
		* @ parseJSON : JSON유효성을 검사합니다.
		*
		**************************************************************************************************/
		parseJSON: function($data){
			var _rvalidtokens = /(,)|(\[|{)|(}|])|"(?:[^"\\\r\n]|\\["\\\/bfnrt]|\\u[\da-fA-F]{4})*"\s*:?|true|false|null|-?(?!0\d)\d+(?:\.\d+|)(?:[eE][+-]?\d+|)/g;
			
			//if( typeof $data !== "string" || $data ) return null;

			// Attempt to parse using the native JSON parser first
			// 첫번째로 네이티브 JSON 파서를 이용해서 분석을 시도한다.
			if ( window.JSON && window.JSON.parse ) {
				// Support: Android 2.3
				// Workaround failure to string-cast null input
				return window.JSON.parse( $data + "" );
			}

			var requireNonComma,
				depth = null,
				str = Trim( $data + "" );

			// Guard against invalid (and possibly dangerous) input by ensuring that nothing remains
			// after removing valid tokens
			// 무효(및 위험) 입력검사에 대해서 유효 토큰을 제외하고 모두 제거한다.
			return str && !Trim( str.replace( _rvalidtokens, function( token, comma, open, close ) {

				// Force termination if we see a misplaced comma
				if ( requireNonComma && comma ) {
					depth = 0;
				}

				// Perform no more replacements after returning to outermost depth
				if ( depth === 0 ) {
					return token;
				}

				// Commas must not follow "[", "{", or ","
				requireNonComma = open || comma;

				// Determine new depth
				// array/object open ("[" or "{"): depth += true - false (increment)
				// array/object close ("]" or "}"): depth += false - true (decrement)
				// other cases ("," or primitive): depth += true - true (numeric cast)
				depth += !close - !open;

				// Remove this token
				return "";
			}) ) ?
				( Function( "return " + str ) )() : 
				function(){ throw new Error( "Invalid JSON: " + $data ) };
		},

		/**************************************************************************************************
		*
		* @ init : XMLHttpRequest 객체를 생성하고, 이벤트 리스너를 등록합니다.
		*
		***************************************************************************`***********************/
		init: function(){
			if(window.ActiveXObject) // IE 이전 버전
				this.xhr = new ActiveXObject("Microsoft.XMLHTTP");
			else
				this.xhr = new XMLHttpRequest();
			
			// !!주의. open() 전 addEventListener
			this.onerror();
			this.timeout();
			this.xhr.onprogress = this.onprogress;
			this.onreadystatechange();
			
			// loading text element create
			this.textElementCreate();
		},

		/**************************************************************************************************
		*
		* @ abort : 전송을 중지합니다.
		*
		**************************************************************************************************/
		abort: function(){
			this.xhr.abort();
		},
		
		/**************************************************************************************************
		*
		* @ destroy : 텍스트 엘리먼트 제거하고 dispose를 실행합니다.
		*
		**************************************************************************************************/
		destroy: function(){
			
			//textElement remove
			var parent = document.body || document.getElementsByTagName('body')[0];
			var child = document.getElementById( this.containerName );
			if( child != null ) parent.removeChild( child );
			
			//abort and clear interval
			if( this.xhr != null ) this.xhr.abort();
			this.stop();
			
			//delete properties and prototype
			this.dispose();
		},
		
		/**************************************************************************************************
		*
		* @ dispose : 객체의 속성과 prototype 을 제거합니다.
		*
		**************************************************************************************************/
		dispose: function(){
			var object = this;
			for( var key in object ){
				if( object.hasOwnProperty(key) ){
					delete object[key];
				}
			}
			delete this.prototype;
			return false;
			
			//완전히 객체를 재사용할 일이 없을때 => 소거한다. ( 주의!! 원본 prototype을 삭제한다 => 기능을 전부 삭제함 )
			/*
			var objectPrototype = object.__proto__;
			for( var key in objectPrototype ){
				if( objectPrototype.hasOwnProperty(key) ){
					delete objectPrototype[key];
				}
			}
			*/
		},
		
		/**************************************************************************************************
		*
		* @ sendRequest : url, param, callback [,type ,method, asyncBool, log] : 실제 send 도달 메서드
		*
		**************************************************************************************************/
		sendRequest: function($url, $param, $callback,       $type, $method, $asyncBool, $log, $repeatBool){
			
			//필수파라메터 (url, param, callback)
			if( typeof $url == "undefined" || $url == "" ) throw new Error( this.getLog($url, $param) + "전달받은 url이 없습니다." );
			if( typeof $param == "undefined" ) throw new Error( this.getLog($url, $param) +"전달받은 param이 없습니다." );
			if( typeof $callback == "undefined" || typeof $callback != "function" ) throw new Error( getLog($url, $param) +"리턴받을 CallBack 함수가 없거나 함수타입이 아닙니다." );
			
			//옵션 파라메터 = 기본값 처리(type, method, asyncBool, log)
			if( typeof $type == "undefined") $type = this.defaultObj.type; // text, json
			if( typeof $method == "undefined" ) $method = this.defaultObj.method; //GET, POST
			if( typeof $asyncBool == "undefined" ) $asyncBool = this.defaultObj.asyncBool; //true, false
			if( typeof $log == "undefined" ) $log = this.defaultObj.log; //true, false
			
			//전역변수
			this.url = $url;
			this.callback = $callback;
			
			this.defaultObj.type = $type;
			this.defaultObj.method = $method;
			this.defaultObj.asyncBool = $asyncBool;
			this.defaultObj.log = $log;

			if( $method.toUpperCase() == "FILE" ){
				this.xhr.open("POST", $url, true);
				// 주의!!. 타입이 FILE 일 경우 Header의 타입을 따로 지정하지 않는다.
				//this.xhr.setRequestHeader("Content-Type", "multipart/form-data");
			}else{
				
				//repeat 값이 true 이면 param 재가공 루틴을 지나간다
				if( typeof $repeatBool == "undefined" || $repeatBool == null || $repeatBool == false ){
					$param = ( $param == null || $param == '' ) ? null : encodeURI($param); 
					this.defaultObj.param = $param; //전역변수 param

					$method = ($method.toLowerCase() == "get") ? "GET" : "POST";

					if($method == "GET" && $param != null) this.url = $url + "?" + $param; // GET 이면 전역변수 url ( + param )
				}

				this.xhr.open($method, this.url, this.defaultObj.asyncBool);
				this.xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
				$param = ($method == "POST") ? $param : null;
			}
			
			// xhr가 open이후 호출한다.
			this.textDisplayHandler(true); // text show (최초에 한번은 실행됩니다)
			this.xhr.send( $param ); /* Send to server */ 
		},
		
		/**************************************************************************************************
		*
		* @ repeat : 전송요청을 반복합니다
		*
		**************************************************************************************************/
		repeat: function($millisecond){

			var $this = this;

			if( $this.defaultObj.method.toUpperCase() == "FILE") throw new Error( $this.getLog($this.url, $this.defaultObj.param) + "FILE 업로드모드에서 repeat은 작동하지 않습니다." );

			if( typeof $millisecond == "undefined" ) $millisecond = $this.defaultObj.millisecond; //파라메터 없다면 옵션 기본값을 사용합니다.

			$this.intervalID = window.setInterval(function(){

				//$this.abort(); //전송 중지시키고
				$this.sendRequest($this.url, $this.defaultObj.param, $this.callback,   $this.defaultObj.type, $this.defaultObj.method, $this.defaultObj.asyncBool, $this.defaultObj.log, true );

			}, $millisecond);
			
		},

		/**************************************************************************************************
		*
		* @ stop : repeat을 중단합니다
		*
		**************************************************************************************************/
		stop: function(){

			var $this = this;

			window.clearInterval( $this.intervalID );
			$this.intervalID = null;
		},

		/**************************************************************************************************
		*
		* @ timeout : 타임아웃
		*
		**************************************************************************************************/
		timeout: function(){
			var $this = this;
			// This is a known IE10 bug : xhr.timeout after the xhr.open(...) line
			try{
				$this.xhr.timeout = $this.defaultObj.timeout;
				$this.xhr.ontimeout = function(e){
					console.log( $this.getLog($this.url, $this.param) + '타임아웃' );
				}
			}catch(e){
				console.error( "[ XhrClass : sendRequest-ajax.js ][ IE 10 bug ] "+e.message );
			}
		},
		
		/**************************************************************************************************
		*
		* @ onprogress : [파일( txt등 )인 경우에 동작] 업로드/다운로드 프로그레스 
		*
		**************************************************************************************************/
		onprogress: function(e){
			var path, percentComplete;
			if (e.lengthComputable) 
			{	
				/*
				e.loaded the bytes browser receive
				e.total the total bytes seted by the header
				*/
				path = e.currentTarget.responseURL;
				var percentComplete = Math.round((e.loaded / e.total) * 100) + ' %';  
				console.info( "[ XhrClass : sendRequest-ajax.js ][ "+path+" ][ "+percentComplete+" ]" );
			} 
		},

		/**************************************************************************************************
		*
		* @ onerror : 에러 핸들러
		*
		**************************************************************************************************/
		onerror: function(){
			var $this = this;
			this.xhr.onerror = function(e){
				console.log( $this.getLog($this.url, $this.param) + '에러' );
			}
		},

		/**************************************************************************************************
		*
		* @ onreadystatechange : 응답 상태를 검사하고 결과값을 callback에 리턴합니다.
		*
		**************************************************************************************************/
		onreadystatechange: function(){
			
			var $this = this;
			this.xhr.onreadystatechange =  function(e){
				
				if($this.xhr.readyState !== XMLHttpRequest.DONE) return;

				if($this.xhr.readyState == 4){  //응답완료 (0~4단계)
					
					$this.textDisplayHandler(false); // text hide : (최초에 한번은 실행됩니다)

					if($this.xhr.status == 200){ //응답이 정상인 경우

						var res;
						
						/**
						TODO :  typeof $this.defaultObj.type
								
								데이터 타입을 검사하여 데이터형식에 따라서 
								
								자동으로 데이터를 처리하도록 하는 로직.

								현재는 switch로 되어있어서 직접 지정해준 타입으로만 결과값을 처리한다.
						**/

						switch($this.defaultObj.type)
						{
							/*
							* text
							*/	
							case 'text' :
								res = $this.xhr.responseText;
							break;

							/*
							* json
							*
							* {"name":"myName"}  =>  res.name 은 myName
							*
							* [{"name":"myName"}]  =>  res[0].name 은 myName
							*/
							case 'json' :
								res = $this.parseJSON( $this.xhr.responseText );
							break;
							
						}

						$this.callback( $this.xhr, res );

						if($this.defaultObj.log) console.log( $this.getLog($this.url, $this.param)+ '로드를 성공했습니다. 성공코드 xhr.status: [' + $this.xhr.status +']' );
					}else
						console.log( $this.getLog($this.url, $this.param)+ '로드를 실패했습니다. 에러코드 xhr.status: [' + $this.xhr.status +']' );
				}
			}
		
		},
		
		/**************************************************************************************************
		*
		* @ getLog : [년-월-일 요일 시간 분 초] URL, PARAM : MSG 를 출력
		*
		**************************************************************************************************/
		getLog: function($url, $param){
			var newDate = new Date();
			var year = newDate.getFullYear();
			var month = newDate.getMonth()+1;
			month = (month < 10 ? "0" : "" ) + month;

			var date = newDate.getDate();
			date = (date < 10 ? "0" : "") + date;
			var dayNames= ["일요일","월요일","화요일","수요일","목요일","금요일","토요일"];
			var dayName = dayNames[newDate.getDay()];

			var seconds = new Date().getSeconds();
			seconds = (seconds < 10 ? "0":"") + seconds;
			
			var minutes = new Date().getMinutes();
			minutes = (minutes < 10 ? "0":"") + minutes;

			var hours = new Date().getHours();
			hours = (hours < 10 ? "0":"") + hours; //24
			
			//$('#time').html( hours  + ' : ' + minutes + ' : ' + seconds );

			var ampm = '';
			if( hours > 12 ) {
				//hours = hours - 12;
				ampm = 'PM';
			}else{
				ampm = 'AM';
			}
			
			return( "["+ year +"-"+ month +"-"+ date +" "+ dayName +" "+ hours +":"+ minutes +":"+ seconds +"] URL["+$url+"], Param["+$param+"] : ");
		}
	}
})();