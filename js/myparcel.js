if (typeof MyParcel == 'undefined') { MyParcel = {}; }

var popup; // Handle to popup window
var consignments = {}; // Hyperlinks to consignments that haven't been processed yet
var locked = false; // Lock to prevent more than one consignment being created at a time

function onClickOnUnprocessedConsignment(event) {
    if (!popup || popup.closed) {
        // User closed the popup
        this.remove(); // Delete the link
    } else {
        popup.focus();
    }
}

MyParcel.PrestashopPlugin = {
    setConsignmentId: function(orderId, timestamp, consignmentId, tracktrace_link, retour){

    	var mypa_div = document.createElement('div');

    	// print checkbox
    	var mypa_check = document.createElement('input');
    	mypa_check.className = 'mypaleft mypacheck';
    	mypa_check.type = 'checkbox';
    	mypa_check.value = consignmentId;

    	// pdf image
    	var mypa_img = document.createElement('img');
    	mypa_img.alt = 'print';
    	mypa_img.src = '/modules/myparcel/images/myparcel_pdf.png';
    	if(retour == 1) mypa_img.src = '/modules/myparcel/images/myparcel_retour.png';
    	mypa_img.style.border = 0;

    	// pdf image link
    	var mypa_link = document.createElement('a');
    	mypa_link.className = 'myparcel-pdf';
    	mypa_link.onclick = new Function('return printConsignments(' + consignmentId + ');');
    	mypa_link.href = '#';
    	mypa_link.appendChild(mypa_img);
    	
    	// tracktrace link
    	var mypa_track = document.createElement('a');
    	mypa_track.target = '_blank';
    	mypa_track.href = tracktrace_link;
    	mypa_track.innerHTML = 'Track&Trace';

    	// shove into DOM
    	mypa_div.appendChild(mypa_check);
    	mypa_div.appendChild(mypa_track);
    	mypa_div.appendChild(mypa_link);
    	var orderdiv = document.getElementById('mypa_exist_' + orderId);
    	orderdiv.insertBefore(mypa_div, orderdiv.firstChild);

    	popup.close();
        locked = false;
    }
};

var lastTimestamp = 0;
function _getTimestamp() {
    var ret = Math.round(new Date().getTime() / 1000);
    if (ret <= lastTimestamp) {
        ret = lastTimestamp + 1; // Make sure it is unique
    }
    return lastTimestamp = ret;
}

function createNewConsignment(orderId, retour)
{
    if (locked) {
        if (!popup || popup.closed) {
            // User closed the popup
        } else {
        	popup.focus();
            return;
        }
    }
    locked = true;
    var timestamp = _getTimestamp();

    var retourparam = '';
    if(retour == true) retourparam = '&retour=true';
    
    popup = window.open(
        '/modules/myparcel/process.php?action=post' + '&order_id=' + orderId + '&timestamp=' + timestamp + retourparam,
        'myparcel',
        'width=730,height=830,dependent,resizable,scrollbars'
        );
    if (window.focus) { popup.focus(); }
    return false;
}

function printConsignments(consignmentList)
{
    if (locked) {
        if (!popup || popup.closed) {
            // User closed the popup
        } else {
        	popup.focus();
            return;
        }
    }
    locked = true;
    var timestamp = _getTimestamp();
    
    popup = window.open(
        '/modules/myparcel/process.php?action=print' + '&consignments=' + consignmentList + '&timestamp=' + timestamp,
        'myparcel',
        'width=415,height=365,dependent,resizable,scrollbars'
        );
    if (window.focus) { popup.focus(); }
    return false;
}

function printConsignmentSelection()
{
	var consignmentList = Array();
    var checkboxes = document.getElementsByClassName('mypacheck');
    for(var i = checkboxes.length - 1; i >= 0; i--)
    {
    	if(checkboxes[i].checked == true && checkboxes[i].value != '')
    	{
    		consignmentList.push(checkboxes[i].value);
    	}
    }
    return (consignmentList.length == 0) ? false : printConsignments(consignmentList.join('|'));
}

function processConsignmentSelection()
{
	var consignmentList = Array();
    var checkboxes = document.getElementsByClassName('mypacheck');
    for(var i = checkboxes.length - 1; i >= 0; i--)
    {
    	if(checkboxes[i].checked == true)
    	{
    		consignmentList.push(checkboxes[i].id.replace('mypa_check_', ''));
    	}
    }
    return (consignmentList.length > 0 && confirm("This will create " + consignmentList.length + " labels.\n\nAre you sure?"))
    ? processConsignments(consignmentList.join('|'))
    : false;
}

function processConsignments(consignmentList)
{
    if (locked) {
        if (!popup || popup.closed) {
            // User closed the popup
        } else {
        	popup.focus();
            return;
        }
    }
    locked = true;
    var timestamp = _getTimestamp();
    
    popup = window.open(
        '/modules/myparcel/process.php?action=process' + '&order_ids=' + consignmentList + '&timestamp=' + timestamp,
        'myparcel',
        'width=415,height=365,dependent,resizable,scrollbars'
        );
    if (window.focus) { popup.focus(); }
    return false;
}