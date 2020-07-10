<!doctype html>
<html>

<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<style>
    #videos-container video{
        width:150px;
        height:120px;
        object-fit:fill;
        position:relative;
        border-radius:7px;
        float:right;
    }
    #videos-remote-container video{
        width:150px;
        height:120px;
        object-fit:fill;
        position:relative;
        border-radius:7px;
        float:right;
    }
</style>
</head>
<body>
	
	<div class="row">
		<div class="col-md-2">
		</div>
		<div class="col-md-4">
			PUBLIC ROOM
		</div>
		<div class="col-md-4">
			<div id="join_request_div" style="display:none;">
				<div class="row">
					<div class="col-md-12">
						<span>do you want to accept the request to join?</span>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<button type="button" id="accept_request" class="btn btn-primary">Accept</button>
					</div>
					<div class="col-md-6">
						<button type="button" id="decline_request" class="btn btn-warning">Decline</button>
					</div>
				</div>
			</div>
			
		</div>
		<div class="col-md-2">
		</div>
	</div>
	<div class="row" style="border-top:1px solid #ececec;margin-bottom:10px; ">
		<div class="col-md-2">
		</div>
		<div class="col-md-8">
			<div class="row">
				<div class="col-md-12">
					<span>Local</span>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12" style="display: flex;align-items: center;justify-content: flex-start;">
					<div id="videos-container" class="my_camera"
			                style="background:#f00;">			
				</div>
			</div>
        </div>
		</div>
		<div class="col-md-2">
		</div>
	</div>
	<div class="row" style="border-top:1px solid #ececec;">
		<div class="col-md-2">
		</div>
		<div class="col-md-8">
			<div class="row">
				<div class="col-md-12">
					<span>Remote</span>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12" style="display: flex;align-items: center;justify-content: flex-start;">
					<div id="videos-remote-container" class="my_camera" style="display:flex;">
				</div>
			</div>
        </div>
		</div>
		<div class="col-md-2">
		</div>
	</div>
	<form method="post" id="private_room_form" action="/php_video_chat/room.php">
		<input type="hidden" id="private_room_id" name="private_room_id" />
		<input type="hidden" id="private_room_host" name="private_room_host" />
	</form>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
	<script src="js/RTC.js"></script>
	<script>
		var socket = io.connect('https://jobbyen.dk:5000');
		function create_UUID(){
			    var dt = new Date().getTime();
			    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			        var r = (dt + Math.random()*16)%16 | 0;
			        dt = Math.floor(dt/16);
			        return (c=='x' ? r :(r&0x3|0x8)).toString(16);
			    });
			    return uuid;
			}
		var DEFAULT_CHANNEL = 'public_room';
    
    		var connection = new RTCMultiConnection();
		    connection.socketURL = 'https://jobbyen.dk:9001/';
		    connection.enableFileSharing = false; // by default, it is "false".
		    connection.userid = create_UUID();
		    var my_id = create_UUID();
		    connection.session = {
		        audio: true,
		        video: true,
		    };
		    connection.sdpConstraints.mandatory = {
		        OfferToReceiveAudio: true,
		        OfferToReceiveVideo: true
		    };
		    connection.onstream = function (event) {
		        console.log(event);
		        console.log(event.type);
		        // document.getElementById('video_container').appendChild(event.mediaElement);

		        if (event.type == 'local') {
		            myLocalVideo = event.mediaElement;
		            document.getElementById('videos-container').appendChild(event.mediaElement);
		        }

		        if (event.type == 'remote') {
		            var html = '<div id="div_' + event.stream.streamid + '" style="display:flex;flex-direction:column;background:#fff;margin-right:10px;">';
		            html+='</div>';
		            $("#videos-remote-container").append(html);
		            document.getElementById('div_' + event.stream.streamid).appendChild(event.mediaElement);
		            var button_html = '<button type="button" onclick="event_request_join(this)" rel="' + event.userid + '" class="btn btn-primary join_request">Request Join';
		            button_html+='</div>';
		            $("#div_" + event.stream.streamid).append(button_html);
		        }
		    };
		    connection.onstreamended = function (event) {
		        console.log('onstreamended');
		        console.log(event);
		        $('#div_' + event.stream.streamid).remove();
		    }

    		connection.openOrJoin(DEFAULT_CHANNEL);
    		function event_request_join(obj){
    			var request_join_id = $(obj).attr('rel');
	    		var data = {
	    			user_id : connection.userid,
	    			request_join_id : request_join_id
	    		}
	    		socket.emit('request_join', data);
    		}
    		var join_id = 0;
	    	socket.on('request_join', (obj)=>{
	    		console.log(obj);
	    		if(connection.userid == obj.request_join_id){
	    			$('#join_request_div').css('display', 'block');
	    			join_id = obj.user_id;
	    		}
	    	});
	    	$('#decline_request').on('click', function(){
	    		var data = {
	    			user_id : connection.userid,
	    			request_join_id : join_id,
	    			accept : 0
	    		}
	    		socket.emit('request_accepted', data);
	    	});
	    	$('#accept_request').on('click', function(){
	    		var private_room_id = create_UUID() + "_private_room";
	    		var data = {
	    			user_id : connection.userid,
	    			request_join_id : join_id,
	    			accept : 1,
	    			private_room_id : private_room_id
	    		}
	    		socket.emit('request_accepted', data);
	    		$('#private_room_id').val(private_room_id);
	    		$('#private_room_host').val(1);
	    		$('#private_room_form').submit();
	    	});
	    	socket.on('request_accepted', (obj)=>{
	    		if(connection.userid == obj.request_join_id){
	    			if(obj.accept == 1){
	    				alert('request accepted!. wait for starting private room!!!');
		    			setTimeout(()=>{
		    				$('#private_room_id').val(obj.private_room_id);
				    		$('#private_room_host').val(0);
				    		$('#private_room_form').submit();
		    			}, 2000);
	    			} else {
	    				alert('request declined!. wait for starting private room!!!');
	    			}
	    			
	    			
	    		}
	    	});
    	
    
    
	</script>
</body>
			
</html>