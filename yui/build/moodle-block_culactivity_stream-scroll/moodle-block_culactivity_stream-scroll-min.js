YUI.add("moodle-block_culactivity_stream-scroll",function(e,t){M.block_culactivity_stream=M.block_culactivity_stream||{},M.block_culactivity_stream.scroll={limitnum:null,count:null,courseid:null,scroller:null,timer:null,init:function(t){e.one(".pages")&&e.one(".pages").hide();var n=e.one(".block_culactivity_stream .reload"),r=e.one(".block_culactivity_stream .header .title h2");r.append(n),n.setStyle("display","inline-block"),e.one(".reload .block_culactivity_stream_reload").on("click",this.reloadblock,this),e.all(".block_culactivity_stream .removelink").on("click",this.removenotification,this),this.scroller=e.one(".block_culactivity_stream .culactivity_stream"),this.scroller.on("scroll",this.filltobelowblock,this),this.limitnum=t.limitnum,this.count=t.count,this.courseid=t.courseid,this.timer=e.later(3e5,this,this.reloadnotifications,[],!0),this.filltobelowblock();var i=M.core.dock.get();i.on(["dock:initialised","dock:itemadded"],function(){e.Array.each(i.dockeditems,function(t){t.on("dockeditem:showcomplete",function(){if(t.get("blockclass")=="culactivity_stream"){var n=e.one(".dockeditempanel_hd .block_culactivity_stream_reload");if(!n){var r=e.one(".block_culactivity_stream .reload").cloneNode(!0),i=e.one("#instance-"+t.get("blockinstanceid")+"-header");i.append(r),r.setStyle("display","inline-block"),n=e.one(".dockeditempanel_hd .block_culactivity_stream_reload")}n&&n.on("click",this.reloadblock,this)}},this)},this)},this)},filltobelowblock:function(){var t=this.scroller.get("scrollHeight"),n=this.scroller.get("scrollTop"),r=this.scroller.get("clientHeight");if(t-(n+r)<10){this.timer.cancel();var i=e.all(".block_culactivity_stream .notifications li").size();if(i>0){var s=e.all(".block_culactivity_stream .notifications li").item(i-1);lastid=s.get("id").split("_")[1]}else lastid=0;this.addnotifications(i,lastid),this.timer=e.later(3e5,this,this.reloadnotifications,[],!0)}},reloadblock:function(e){e.preventDefault(),this.reloadnotifications(e)},addnotifications:function(t,n){if(t<=this.count){this.scroller.detach("scroll"),e.one(".block_culactivity_stream_reload").setStyle("display","none"),e.one(".block_culactivity_stream_loading").setStyle("display","inline-block");var r={sesskey:M.cfg.sesskey,limitfrom:0,limitnum:this.limitnum,lastid:n,newer:!1,courseid:this.courseid};e.io(M.cfg.wwwroot+"/blocks/culactivity_stream/scroll_ajax.php",{method:"POST",data:build_querystring(r),context:this,on:{success:function(t,n){var r=e.JSON.parse(n.responseText);r.error?this.timer.cancel():e.one(".block_culactivity_stream .notifications").append(r.output),r.end||this.scroller.on("scroll",this.filltobelowblock,this),e.one(".block_culactivity_stream_loading").setStyle("display","none"),e.one(".block_culactivity_stream_reload").setStyle("display","inline-block")},failure:function(){e.one(".block_culactivity_stream_loading").setStyle("display","none"),e.one(".block_culactivity_stream_reload").setStyle("display","inline-block"),this.timer.cancel()}}})}},reloadnotifications:function(){var t=0;this.scroller.one("li")&&(t=this.scroller.one("li").get("id").split("_")[1]),e.one(".block_culactivity_stream_reload").setStyle("display","none"),e.one(".block_culactivity_stream_loading").setStyle("display","inline-block");var n={sesskey:M.cfg.sesskey,lastid:t,courseid:this.courseid};e.io(M.cfg.wwwroot+"/blocks/culactivity_stream/reload_ajax.php",{method:"POST",data:build_querystring(n),context:this,on:{success:function(t,n){var r=e.JSON.parse(n.responseText);r.error?this.timer.cancel():(e.one(".block_culactivity_stream .notifications").prepend(r.output),this.count=this.count+r.count),e.one(".block_culactivity_stream_loading").setStyle("display","none"),e.one(".block_culactivity_stream_reload").setStyle("display","inline-block")},failure:function(){e.one(".block_culactivity_stream_loading").setStyle("display","none"),e.one(".block_culactivity_stream_reload").setStyle("display","inline-block"),this.timer.cancel()}}})},removenotification:function(t){t.preventDefault();var n=t.target,r=n.get("href").split("?"),i=r[1],s=e.QueryString.parse(i);e.io(M.cfg.wwwroot+"/blocks/culactivity_stream/remove_ajax.php",{method:"POST",data:i,context:this,on:{success:function(){e.one("#m_"+s.remove).next().remove(!0),e.one("#m_"+s.remove).remove(!0)}}})}}},"@VERSION@",{requires:["base","node","io","json-parse","dom-core","querystring","event-custom","moodle-core-dock"]});
