// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Scroll functionality.
 *
 * @package   block_culactivity_stream
 * @copyright 2014 onwards Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.block_culactivity_stream = M.block_culactivity_stream || {};
M.block_culactivity_stream.scroll = {

    limitnum: null,
    count: null,
    courseid: null,
    scroller: null,
    timer: null,

    init: function(params) {

        if (Y.one('.pages')) {
            Y.one('.pages').hide();
        }

        var doc = Y.one(Y.config.doc);

        try {
            var reloaddiv = Y.one('.block_culactivity_stream .reload');
            var block = Y.one('.block_culactivity_stream');
            var id = block.get('id');
            id = id.replace('inst', '');
            var h2 = Y.one('#instance-' + id + '-header');
            h2.append(reloaddiv);
            reloaddiv.setStyle('display', 'inline-block');
            doc.delegate('click', this.reloadblock, '.block_culactivity_stream_reload', this);
        } catch (e) {
            Y.log('Problem adding reload button');
        }

        doc.delegate('click', this.removenotification, '.block_culactivity_stream .removelink', this);
        this.scroller = Y.one('.block_culactivity_stream .culactivity_stream');
        this.scroller.on('scroll', this.filltobelowblock, this);
        this.limitnum = params.limitnum;
        this.count = params.count;
        this.courseid = params.courseid;
        this.returnurl = params.returnurl;
        this.instanceid = params.instanceid;
        // Refresh the feed every 5 mins.
        this.timer = Y.later(1000 * 60 * 5, this, this.reloadnotifications, [], true);
        this.filltobelowblock();
    },

    filltobelowblock: function() {
        var scrollHeight = this.scroller.get('scrollHeight');
        var scrollTop = this.scroller.get('scrollTop');
        var clientHeight = this.scroller.get('clientHeight');
        var lastid;

        if ((scrollHeight - (scrollTop + clientHeight)) < 10) {
            // Pause the automatic refresh
            this.timer.cancel();
            var num = Y.all('.block_culactivity_stream .notifications li').size();
            if (num > 0) {
                var lastitem = Y.all('.block_culactivity_stream .notifications li').item(num - 1);
                lastid = lastitem.get('id').split('_')[1];
            } else {
                lastid = 0;
            }
            this.addnotifications(num, lastid);
            // Start the automatic refresh again now we have the correct last item
            this.timer = Y.later(1000 * 60 * 5, this, this.reloadnotifications, [], true);
        }
    },

    reloadblock: function(e) {
        e.preventDefault();
        this.reloadnotifications(e);
    },

    addnotifications: function(num, lastid) {

        if (num <= this.count) {
            // disable the scroller until this completes
            this.scroller.detach('scroll');
            Y.one('.block_culactivity_stream_reload').setStyle('display', 'none');
            Y.one('.block_culactivity_stream_loading').setStyle('display', 'inline-block');

            var params = {
                sesskey : M.cfg.sesskey,
                limitnum: this.limitnum,
                lastid : lastid,
                newer: false,
                courseid: this.courseid,
                returnurl: this.returnurl,
                instanceid: this.instanceid
            };

            Y.io(M.cfg.wwwroot + '/blocks/culactivity_stream/scroll_ajax.php', {
                method: 'POST',
                data: window.build_querystring(params),
                context: this,
                on: {
                    success: function(id, e) {
                        var data = Y.JSON.parse(e.responseText);
                        if (data.error) {
                            this.timer.cancel();
                        } else {
                            Y.one('.block_culactivity_stream .notifications').append(data.output);
                        }
                        // renable the scroller if there are more notifications
                        if (!data.end) {
                            this.scroller.on('scroll', this.filltobelowblock, this);
                        }
                        Y.one('.block_culactivity_stream_loading').setStyle('display', 'none');
                        Y.one('.block_culactivity_stream_reload').setStyle('display', 'inline-block');
                    },
                    failure: function() {
                        // error message
                        Y.one('.block_culactivity_stream_loading').setStyle('display', 'none');
                        Y.one('.block_culactivity_stream_reload').setStyle('display', 'inline-block');
                        this.timer.cancel();
                    }
                }
            });
        }
    },

    reloadnotifications: function() {
        var lastid = 0;

        if (this.scroller.one('li')) {
            lastid = this.scroller.one('li').get('id').split('_')[1];
        }

        Y.one('.block_culactivity_stream_reload').setStyle('display', 'none');
        Y.one('.block_culactivity_stream_loading').setStyle('display', 'inline-block');

        var params = {
            sesskey : M.cfg.sesskey,
            lastid : lastid,
            courseid: this.courseid,
            returnurl: this.returnurl,
            instanceid: this.instanceid
        };

        Y.io(M.cfg.wwwroot + '/blocks/culactivity_stream/reload_ajax.php', {
            method: 'POST',
            data: window.build_querystring(params),
            context: this,
            on: {
                success: function(id, e) {
                    var data = Y.JSON.parse(e.responseText);
                    if (data.error) {
                        this.timer.cancel();
                    } else {
                        Y.one('.block_culactivity_stream .notifications').prepend(data.output);
                        this.count = this.count + data.count;
                    }
                    Y.one('.block_culactivity_stream_loading').setStyle('display', 'none');
                    Y.one('.block_culactivity_stream_reload').setStyle('display', 'inline-block');
                },
                failure: function() {
                    // error message
                    Y.one('.block_culactivity_stream_loading').setStyle('display', 'none');
                    Y.one('.block_culactivity_stream_reload').setStyle('display', 'inline-block');
                    this.timer.cancel();
                }
            }
        });

    },

    removenotification: function(e) {
        e.preventDefault();
        var link = e.target;
        var href = link.get('href').split('?');
        //var url = href[0];
        var querystring = href[1];
        // returns an object with params as attributes
        var params = Y.QueryString.parse(querystring);

        Y.io(M.cfg.wwwroot + '/blocks/culactivity_stream/remove_ajax.php', {
            method: 'POST',
            data: querystring,
            context: this,
            on: {
                success: function() {
                    Y.one('#m_' + params.remove).next().remove(true);
                    Y.one('#m_' + params.remove).remove(true);
                }
            }
        });
    }

};