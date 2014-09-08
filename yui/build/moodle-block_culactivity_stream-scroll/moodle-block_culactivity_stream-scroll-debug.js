YUI.add('moodle-block_culactivity_stream-scroll', function (Y, NAME) {

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
    reloader: null,
    timer: null,

    init: function(params) {

        if (Y.one('.pages')) {
            Y.one('.pages').hide();
        }

        this.reloader = Y.one('.block_culactivity_stream #block_culactivity_stream_reload');
        this.reloader.on('click', this.reloadblock, this);
        Y.all('.block_culactivity_stream .removelink').on('click', this.removenotification, this);

        this.scroller = Y.one('.block_culactivity_stream .culactivity_stream');
        this.scroller.on('scroll', this.filltobelowblock, this);
        this.limitnum = params.limitnum;
        this.count = params.count;
        this.courseid = params.courseid;
        // Refresh the feed every 5 mins
        this.timer = Y.later(1000 * 60 * 5, this, this.reloadnotifications, [], true);
        this.filltobelowblock();
    },

    filltobelowblock: function() {
        var scrollHeight = this.scroller.get('scrollHeight');
        var scrollTop = this.scroller.get('scrollTop');
        var clientHeight = this.scroller.get('clientHeight');

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
            Y.one('#loadinggif').setStyle('display', 'inline-block');

            var params = {
                sesskey : M.cfg.sesskey,
                limitfrom: 0,
                limitnum: this.limitnum,
                lastid : lastid,
                newer: false,
                courseid: this.courseid
            };

            Y.io(M.cfg.wwwroot + '/blocks/culactivity_stream/scroll_ajax.php', {
                method: 'POST',
                data: build_querystring(params),
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
                        Y.one('#loadinggif').setStyle('display', 'none');
                    },
                    failure: function() {
                        // error message
                        Y.one('#loadinggif').setStyle('display', 'none');
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

        Y.one('#loadinggif').setStyle('display', 'inline-block');

        var params = {
            sesskey : M.cfg.sesskey,
            lastid : lastid,
            courseid: this.courseid
        };

        Y.io(M.cfg.wwwroot + '/blocks/culactivity_stream/reload_ajax.php', {
            method: 'POST',
            data: build_querystring(params),
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
                    Y.one('#loadinggif').setStyle('display', 'none');
                },
                failure: function() {
                    // error message
                    Y.one('#loadinggif').setStyle('display', 'none');
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

}, '@VERSION@', {"requires": ["base", "node", "io", "json-parse", "dom-core", "querystring"]});
