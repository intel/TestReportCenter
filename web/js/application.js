/*
 * This file is part of meego-test-reports
 *
 * Copyright (C) 2010 Nokia Corporation and/or its subsidiary(-ies).
 *
 * Authors: Sami Hangaslammi <sami.hangaslammi@leonidasoy.fi>
 * 			Jarno Keskikangas <jarno.keskikangas@leonidasoy.fi>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public License
 * version 2.1 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA
 * 02110-1301 USA
 *
 */

var bugzillaCache = [];

function activateSuggestionLinks(target) {
    $(target).each(function(i, node) {
        var $node = $(node);
        $node.find(".suggestions a").each(function(i, a) {
            var target = $node.find('input');
            $(a).data("target", target).click(applySuggestion);
        });
    });
}

function applySuggestion() {
    var $this = $(this);
    $this.data("target").val($this.text());
    return false;
}

function capitalize(s) {
  return s.charAt(0).toUpperCase() + s.substr(1).toLowerCase();
}

function toTitlecase(s) {
  // TODO: s may be undefined when editing report
  return s.replace(/\w\S*/g, capitalize);
}

function htmlEscape(s) {
    s = s.replace('&', '&amp;');
    s = s.replace('<', '&lt;');
    s = s.replace('>', '&gt;');
    return s;
}

renderSeriesGraphs = function(selector) {
    var $selector = $(selector);

    var renderGraph = function(index, div) {
        var $div = $(div);
        var $modal_info = $div.prev();
        var values = eval($div.text());
        if (values.length > 0) {
            if (values.length == 1) {
                values[1] = values[0];
            }
            var id = $div.attr("id");
            //var $canvas = $('<canvas id="'+id+'" width="287" height="46"/>');
            var canvas = document.createElement("canvas");
            // if it is IE
            if (typeof G_vmlCanvasManager != 'undefined') {
                canvas = G_vmlCanvasManager.initElement(canvas);
            }

            var $canvas = $(canvas);
            $canvas.attr("id", id);
            $canvas.attr("width", "287");
            $canvas.attr("height", "46");

            var bg = $div.parent().css("background-color");
            $div.replaceWith($canvas);

            g = new Bluff.Line(id, '287x46');
            g.tooltips = false;
            g.sort = false;

            g.hide_title  = true;
            g.hide_dots   = true;
            g.hide_legend = true;
            g.hide_mini_legend = true;
            g.hide_line_numbers = true;
            g.hide_line_markers = true;

            g.line_width = 1;

            g.set_theme({
                colors: ['#acacac'],
                marker_color: '#dedede',
                font_color: '#6f6f6f',
                background_colors: [bg, bg]
            });

            g.data("values", values, "#8888dd");
            g.draw();

            $canvas.click(function() {
                // Render NftTrendGraph, the same that is shown in See latest
                // -mode when clickin the measurement value
                if ($div.hasClass('nft_history')) {
                    var m_id = id.match("[0-9]{1,}$");
                    renderNftTrendGraph(m_id);
                // Open NFT serial measurement graph
                } else if ($div.hasClass('nft_serial_history')) {
                    renderNftSerialTrendGraph($modal_info);
                } else {
                    renderModalGraph($modal_info);
                }
            });
        }
    };

    var renderModalGraph = function(elem) {
        var $elem = $(elem);
        var title = $elem.find(".modal_graph_title").text();
        var xunit = $elem.find(".modal_graph_x_unit").text();
        var yunit = $elem.find(".modal_graph_y_unit").text();
        var data  = eval($elem.find(".modal_graph_data").text());

        var $modal = $(".nft_drilldown_dialog");
        var $close = $modal.find(".modal_close");

        $modal.find("h1").text(title);
        $modal.jqm({modal:true, toTop:true});
        $modal.jqmAddClose($close);
        $modal.jqmShow();

        //var $graph = $modal.find(".nft_drilldown_graph");
        var graph = document.getElementById("nft_drilldown_graph");
        var updateLabels = function() {
            $(graph).find("div").each(function(idx,e) {
                var $e = $(e);
                if ($e.css.has("top")) {
                    $e.css("width", parseInt($e.css("width"))+10);
                    $e.css("left", -10);
                    $e.text($e.text() + yunit);
                } else if ($e.css("text-align") == "center") {
                    $e.css("width", parseInt($e.css("width"))+15);
                    $e.text($e.text() + xunit);
                }
            });
        };
        dyg = new Dygraph(graph, data, {
          labels:[xunit, yunit],
          drawCallback: updateLabels,
          includeZero: true
          //xValueFormatter: function(x) {return x + xunit;}
          //yValueFormatter: function(y) {return y + yunit;}
        });

    };

    var renderNftSerialTrendGraph = function(elem) {
        var updateNftSerialTrendGraphData = function(dyg) {
            var $modal = $("#nft_series_history_dialog");
            var visibility = [true, true, true, true];

            // Change Dygraph series visibility based on the checkboxes
            // Note: the checkboxes have value attribute set, and the order
            // needs to match with the CSV columns
            $modal.find(":checkbox").each(function(i, node) {
                visibility[parseInt(node.value)] = node.checked;
                });

            dyg.updateOptions({ visibility: visibility });
        };

        var $modal = $("#nft_series_history_dialog");

        var $elem = $(elem);
        var title = $elem.find(".nft_serial_trend_graph_title").text();
        var data = $elem.children(".nft_serial_trend_graph_data").text();

        $modal.find("h1").text(title);
        $modal.jqmShow();

        if (!data) {
            // Set some data for the graph to work
            var data =
                "Date,Max. value,Avg. value,Med. value,Min. value\n0,0,0,0,0";
        }

        var graph = document.getElementById("nft_series_history_graph");
        dyg = new Dygraph(graph, data, {
                              colors: ["#2a7438",
                                       "#6c3d0f",
                                       "#233a84",
                                       "#bb2825"]
                          });

        // Serial trend dialog checkboxes
        $modal.find(':checkbox').change(function() {
            updateNftSerialTrendGraphData(dyg);
        });
        updateNftSerialTrendGraphData(dyg);
    };

    $selector.each(renderGraph);
};

var renderNftTrendGraph = function(m_id) {
    var $modal = $("#nft_trend_dialog");
    var $elem = $("#nft-trend-data-" + m_id.toString());

    var data = $elem.children(".nft_trend_graph_data").text();
    // Don't break the whole thing if there's no data - now one can
    // at least close the window
    if (!data) {
        data = "Date,Value\n0,0";
    }

    var title = $elem.find(".nft_trend_graph_title").text();
    var unit = $elem.find(".nft_trend_graph_unit").text();
    var graph = document.getElementById("nft_trend_graph");

    $modal.find("h1").text(title);
    $modal.jqmShow();

    dyg = new Dygraph(graph, data);
};

(function($) {

    /*
     * Auto-growing textareas; technique ripped from Facebook
     */
    $.fn.autogrow = function(options) {

        this.filter('textarea').each(function() {

            var $this = $(this),
                    minHeight = $this.height(),
                    lineHeight = $this.css('lineHeight');

            var shadow = $('<div></div>').css({
                position:   'absolute',
                top:        -10000,
                left:       -10000,
                width:      $(this).width() - parseInt($this.css('paddingLeft')) - parseInt($this.css('paddingRight')),
                fontSize:   $this.css('fontSize'),
                fontFamily: $this.css('fontFamily'),
                lineHeight: $this.css('lineHeight'),
                resize:     'none'
            }).appendTo(document.body);

            var update = function() {

                var times = function(string, number) {
                    var _res = '';
                    for (var i = 0; i < number; i ++) {
                        _res = _res + string;
                    }
                    return _res;
                };

                var val = this.value.replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/&/g, '&amp;')
                        .replace(/\n$/, '<br/>&nbsp;')
                        .replace(/\n/g, '<br/>')
                        .replace(/ {2,}/g, function(space) {
                    return times('&nbsp;', space.length - 1) + ' '
                });

                shadow.html(val);
                $(this).css('height', Math.max(shadow.height() + 20, minHeight));

            }

            $(this).change(update).keyup(update).keydown(update);

            update.apply(this);

        });

        return this;

    }

})(jQuery);

function applyBugzillaInfo(node, info) {
    var $node = $(node);
    if (info == undefined) {
        $node.addClass("invalid");
    } else {
        var status = info.status;
        if (status == 'Resolved' || status == 'Released' || status == 'Verified') {
            $node.addClass("resolved");
            status = info.resolution;
        } else {
            $node.addClass("unresolved");
        }

        var text = info.summary;
        if ($node.closest('td.testcase_notes').length != 0) {
            text = text + " (" + status + ")";
            $node.attr("title", text);
        } else if($node.hasClass("bugzilla_append")) {
            text = text + " (" + status + ")";
            $node.after("<span> - "  + text +"</span>");
        } else {
            $node.text(text);
            $node.attr("title", status);
        }
    }
    $node.removeClass("fetch");
}

// Rui Fetch bug info
function fetchBugzillaInfo() {
    var bugIds = [];
    var searchUrl = "/fetch_bugzilla_data";

    var links = $('.bugzilla.fetch');
    links.each(function(i, node) {
        var id = $.trim($(node).text());
        if (id in bugzillaCache) {
            applyBugzillaInfo(node, bugzillaCache[id]);
        } else {
            if ($.inArray(id, bugIds) == -1) bugIds.push(id);
        }
    });

    if (bugIds.length == 0) return;
    $.getJSON(searchUrl, "bugids[]=" + bugIds.toString(), function(data) {
        var hash = [];
        for (var i = 1; i < data.length; i++) {
            var row = data[i];
            var id = row[0];
            var summary = row[1];
            var status = row[2];
            var resolution = row[3];
            hash[id.toString()] = {summary: row[1], status:row[2], resolution:row[3]};
        }

        $('.bugzilla.fetch').each(function(i, node) {
            var info;
            var id = $.trim($(node).text());
            if (id in bugzillaCache) {
                info = bugzillaCache[id];
            } else {
                info = hash[id];
                if (info != undefined) {
                    bugzillaCache[id] = info;
                }
            }
            applyBugzillaInfo(node, info);
        });
    });
}


function setTableLoaderSize(tableID, loaderID) {
                t = $(tableID);
                h = t.height();
                $(loaderID).height(h);
        }

function filterResults(rowsToHide, typeText) {
    var updateToggle = function($tbody, $this) {
        var count = $tbody.find("tr:hidden").length;
        if(count > 0) {
            $this.text("+ see " + count + " " + typeText);
        } else {
            $this.text("- hide " + typeText);
        }
        if($tbody.find(rowsToHide).length == 0) {
            $this.hide();
        }
    }

    var updateToggles = function() {
        $("a.see_all_toggle").each(function() {
          $tbody = $(this).parents("tbody").next("tbody");
          updateToggle($tbody, $(this));
        });
    }


    $(".see_feature_build_button").click(function(){
      $("a#detailed_feature.sort_btn").removeClass("active");
      $("#test_results_by_feature").hide();
      $('#test_feature_history_results').hide();
      $feature_build.show();
      $(this).addClass("active");
      return false;
    });

    $(".see_feature_comment_button").click(function(){
      $("a#detailed_feature.sort_btn").removeClass("active");
      $("#test_feature_build_results").hide();
      $feature_details.show();
      $(this).addClass("active");
      return false;
    });

    $(".see_the_same_build_button").click(function(){
      $("a#detailed_case.sort_btn").removeClass("active");
      $("#detailed_functional_test_results").hide();
      $build.show();
      $build.find(".see_the_same_build_button").addClass("active");
      return false;
    });
   
    $(".see_feature_history_button").click(function(){
        $("a#detailed_feature.sort_btn").removeClass("active");
        $("#test_results_by_feature").hide();
        $("#test_feature_build_results").hide();
        $feature_history.show();
        $(this).addClass("active");
        return false;
    });

    $(".see_feature_comment_button").click(function(){
        $("a.see_feature_history_button").removeClass("active");
        $(this).addClass("active");
        $feature_history.hide();
        $feature_details.show();
        return false;
    }); 

    $(".see_history_button").click(function(){
      $("a#detailed_case.sort_btn").removeClass("active");
      $("#detailed_functional_test_results").hide();
      $history.show();
      $history.find(".see_history_button").addClass("active");
      return false;
    });

    $(".see_all_button").click(function(){
        $("a#detailed_case.sort_btn.non_nft_button").removeClass("active");
        $(this).addClass("active");
        $(rowsToHide).show();
        updateToggles();
        return false;
    });

    $(".see_all_comparison_button").click(function(){
        $("a.see_only_failed_comparison_button.sort_btn").removeClass("active");
        $(this).addClass("active");
        $(rowsToHide).show();
        updateToggles();
        return false;
    });

    $(".see_only_failed_button").click(function(){
        $("a#detailed_case.sort_btn.non_nft_button").removeClass("active");
        $("a#detailed_case.sort_btn").removeClass("active");
        $(this).addClass("active");
        $(rowsToHide).hide();
        updateToggles();
        return false;
    });

    $(".see_only_failed_comparison_button").click(function(){
        $("a.see_all_comparison_button.sort_btn").removeClass("active");
        $(this).addClass("active");
        $(rowsToHide).hide();
        updateToggles();
        return false;
    });

    updateToggles();
    $("a.see_all_toggle").each(function() {
        $(this).click(function(index, item) {
            var $this = $(this);
            $tbody = $this.parents("tbody").next("tbody");
            $tbody.find(rowsToHide).toggle();
            updateToggle($tbody, $this);
            return false;
        });
    });

    var $detail  = $("table.detailed_results").first();
    var $history = $("table.detailed_results.history");
    var $build = $("table.detailed_results.build");
    var $feature_details = $("table.feature_detailed_results").first();
    var $feature_history = $("table.feature_detailed_results_with_passrate_history");
    var $feature_build = $("table.feature_detailed_results_with_build_id")

    $history.find(".see_all_button").click(function(){
        $history.hide();
        $detail.show();
        $detail.find(".see_all_button").click();
    });
    $history.find(".see_only_failed_button").click(function(){
        $history.hide();
        $detail.show();
        $detail.find(".see_only_failed_button").click();
    });
    $history.find(".see_the_same_build_button").click(function(){
        $history.hide();
        $build.show();
        $detail.find(".see_the_same_build_button").click();
    });
    $build.find(".see_all_button").click(function(){
        $build.hide();
        $detail.show();
        $detail.find(".see_all_button").click();
    });
    $build.find(".see_only_failed_button").click(function(){
        $build.hide();
        $detail.show();
        $detail.find(".see_only_failed_button").click();
    });
    $build.find(".see_history_button").click(function(){
        $build.hide();
        $history.show();
        $detail.find(".see_the_history_button").click();
    });
    $feature_history.find(".see_feature_comment_button").click(function(){
        $feature_history.hide();
        $feature_build.hide();
        $feature_details.show();
        $feature_details.find(".see_feature_comment_button").click();
    });
    $feature_history.find(".feature_detailed_results_with_build_id").click(function(){
        $feature_history.hide();
        $feature_details.hide();
        $feature_build.show();
        $feature_build.find(".feature_detailed_results_with_build_id").click();
    });
    $feature_build.find(".see_feature_comment_button").click(function(){
        $feature_build.hide();
        $feature_history.hide();
        $feature_details.show();
        $feature_details.find(".see_feature_comment_button").click();
    });
    $feature_build.find(".feature_detailed_results_with_passrate_history").click(function(){
        $feature_build.hide();
        $feature_details.hide();
        $feature_history.show();
        $feature_history.find(".feature_detailed_results_with_passrate_history").click();
    });

    // NFT history

    var $nft_detail  = $("table.non-functional_results.detailed_results").first();
    var $nft_history = $("table.non-functional_results.detailed_results.history");

    $(".see_nft_history_button").click(function(){
      $nft_detail.hide();
      $nft_history.show();
      return false;
    });

    $(".see_latest_button").click(function(){
        $nft_history.hide();
        $nft_detail.show();
        return false;
    });
}
