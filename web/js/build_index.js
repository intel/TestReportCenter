/* DO NOT MODIFY. This file was compiled Wed, 21 Dec 2011 14:39:18 GMT from
 * /home/rui/qareports/huangruis-qa-reports/app/coffeescripts/build_index.coffee
 */

$(document).ready(function() {
  var $editables, $navigation, cancel, directives, edit, editMode, end_edit, initInplaceEdit, initTabs, save, submit, undo, viewMode;
  $navigation = $('#report_navigation');
  $editables = $();
  directives = {
    profiles: {
      'name@href': function() {
        return this.url;
      },
      build_ids: {
        'name@href': function() {
          return this.url;
        },
        testsets: {
          'name@href': function() {
            return this.url;
          },
          products: {
            'name@href': function() {
              return this.url;
            }
          }
        }
      }
    }
  };
  undo = function(input) {
    var $input;
    $input = $(input);
    return $editables.text($input.data('undo'));
  };
  edit = function(event) {
    var $link;
    event.preventDefault();
    $link = $(this);
    $link.hide();
    $link.next('input.inplace-edit').show().focus().val($link.text()).data('undo', $link.text());
    $editables = $('.products a').filter(function() {
      return $(this).text() === $link.text();
    }).add($link);
    return $editables.addClass('being_edited');
  };
  end_edit = function(input) {
    var $input;
    $input = $(input);
    $input.hide();
    $input.prev('a.name').show();
    $editables.removeClass('being_edited');
    return $editables = $();
  };
  cancel = function(input) {
    undo(input);
    return end_edit(input);
  };
  submit = function(input) {
    save(input);
    return end_edit(input);
  };
  save = function(input) {
    var $input, data, post_url, val;
    $input = $(input);
    post_url = $input.attr('data-url');
    val = $input.val();
    data = {
      "authenticity_token": auth_token,
      "_method": "put",
      "new_value": val
    };
    return $.post(post_url, data, function(res, status) {
      var release, scope, _, _ref;
      _ref = location.hash.split('/'), _ = _ref[0], release = _ref[1], scope = _ref[2];
      return $.get("/" + release + "/build/" + scope + ".json", function(view_model) {
        $navigation.render(view_model, directives).show();
        return editMode();
      });
    });
  };
  editMode = function(event) {
    if (event != null) {
      event.preventDefault();
    }
    $('#index_page').addClass('editing');
    $navigation.find('tbody a.name').addClass('editable_text').show();
    return $navigation.find('a.compare').hide();
  };
  viewMode = function(event) {
    event.preventDefault();
    $('#index_page').removeClass('editing');
    $navigation.find('tbody a.name').removeClass('editable_text');
    $navigation.find('tbody a.name').show();
    return $navigation.find('a.compare').filter(function(index) {
      return $(this).attr('href').length > 0;
    }).show();
  };
  initInplaceEdit = function() {
    var $inputs, product_titles;
    $('#home_edit_link').click(editMode);
    $('#home_edit_done_link').click(viewMode);
    $('#index_page.editing #report_navigation tbody a.name').live('click', edit);
    $inputs = $navigation.find('input.inplace-edit');
    $inputs.live('blur', function() {
      return cancel(this);
    });
    $inputs.live('keyup', function(key) {
      if (key.keyCode === 27) {
        return cancel(this);
      }
    });
    $inputs.live('keyup', function(key) {
      if (key.keyCode === 13) {
        return submit(this);
      }
    });
    $('input.inplace-edit').live('keyup', function() {
      return $editables.text($(this).val());
    });
    product_titles = '#index_page.editing .products a';
    $(product_titles).live('mouseover', function() {
      var product_name;
      if ($editables.length === 0) {
        product_name = $(this).text();
        return $(product_titles).filter(function() {
          return $(this).text() === product_name;
        }).addClass('to_be_edited');
      }
    });
    return $(product_titles).live('mouseout', function() {
      return $(product_titles).removeClass('to_be_edited');
    });
  };
  initTabs = function() {
    var release, scope, _, _ref;
    $('.tabs').select(function(event) {
      var selected, target;
      target = $(event.target);
      selected = target.attr('selected');
      return target.find("a[href='" + selected + "']").parent().addClass('current').siblings().removeClass('current');
    });
    $('.tabs').click(function(event) {
      event.preventDefault();
      return $(this).attr('selected', $(event.target).attr('href')).select().change();
    });
    $('.tabs').change(function(event) {
      var release_path, scope_path;
      release_path = $('#release_filters').attr('selected');
      scope_path = $('#report_filters').attr('selected');
      return Spine.Route.navigate(release_path + scope_path);
    });
    _ref = location.hash.split('/'), _ = _ref[0], release = _ref[1], scope = _ref[2];
    if (release && scope) {
      $("#release_filters a[href='/" + release + "'").click();
      return $("#report_filters a[href='/" + scope + "'").click();
    } else {
      $("#release_filters .current a").click();
      return $("#report_filters .current a").click();
    }
  };
  Spine.Route.add({
    "/:release/:scope": function(params) {
      return $.get("/" + params.release + "/build/" + params.scope + ".json", function(view_model) {
        return $navigation.render(view_model, directives).show();
      });
    }
  });
  Spine.Route.setup();
  initInplaceEdit();
  return initTabs();
});
