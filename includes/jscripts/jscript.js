/*-------------------------------------------------------+
 | PHPFusion Content Management System
 | Copyright (C) PHP Fusion Inc
 | https://phpfusion.com/
 +--------------------------------------------------------+
 | Filename: jscript.js
 | Author: Core Development Team
 +--------------------------------------------------------+
 | This program is released as free software under the
 | Affero GPL license. You can redistribute it and/or
 | modify it under the terms of this license which you
 | can read by viewing the included agpl.txt or online
 | at www.gnu.org/licenses/agpl.html. Removal of this
 | copyright header is strictly prohibited without
 | written permission from the original author(s).
 +--------------------------------------------------------*/
/**
 * Flipbox
 *
 * @param boxname
 */
function flipBox(boxname) {
    if (document.images["b_" + boxname].src.indexOf("_on") === -1) {
        let a = document.images["b_" + boxname].src.replace("_off", "_on");
        document.getElementById("box_" + boxname).style.display = "none";
        if (document.getElementById("box_" + boxname + "_diff")) {
            document.getElementById("box_" + boxname + "_diff").style.display = "block"
        }
        document.images["b_" + boxname].src = a;
        document.cookie = "fusion_box_" + boxname + "= none";
    } else {
        let a = document.images["b_" + boxname].src.replace("_on", "_off");
        document.getElementById("box_" + boxname).style.display = ""; //removed 'block'
        if (document.getElementById("box_" + boxname + "_diff")) {
            document.getElementById("box_" + boxname + "_diff").style.display = "none"
        }
        document.images["b_" + boxname].src = a;
        document.cookie = "fusion_box_" + boxname + "= block";
    }
}

/**
 * Tool to trim text
 * Usage:
 *     data-trim-text='30' - 30 is text length
 *     $('[data-trim-text]').trim_text(); - function initialization
 */
$.fn.trim_text = function () {
    return this.each(function () {
        let length = $(this).data("trim-text"), newtext, dots;

        dots = "";
        if ($(this).text().length > length) dots = "...";
        newtext = $(this).text().substr(0, length) + dots;

        return $(this).text(newtext);
    });
};

/**
 * Tool to scroll the window to a designated ID
 * @param hash - ID only
 */
function scrollTo(hash) {
    let hashDOM = $('#' + hash);
    if (hashDOM.length) {
        let scrollNav = hashDOM.offset().top;
        $(document.body).animate({'scrollTop': scrollNav - hashDOM.outerHeight(true)}, 600);
    }
}

/**
 * Tool to copy source element's width to target element.
 * @param source - # or .class element to copy from
 * @param target - # or .class element to copy to
 */
function copyWidth(source, target) {
    let width = $(source).width();
    $(target).width(width);
}

/**
 * Jquery html_entities_decode
 * @param encodedString
 * @returns {*}
 */
function decodeEntities(encodedString) {
    let textArea = document.createElement('textarea');
    textArea.innerHTML = encodedString;
    return textArea.value;
}

/**
 * addText
 *
 * @param textarea
 * @param text1
 * @param text2
 * @param formname
 */
let addText = function (textarea, text1, text2, formname) {

    textarea = textarea === undefined ? "message" : textarea;

    if (formname === undefined) {
        let txtarea = $('textarea[name="'+textarea+'"]');
        formname = txtarea.closest("form").attr("name");
    }

    let element = document.forms[formname].elements[textarea];

    element.focus();

    if (document.selection) {
        let c = document.selection.createRange();
        c.text = text1 + c.text + text2;
        return false;
    } else {
        if (element.setSelectionRange) {
            let b = element.selectionStart, g = element.selectionEnd;
            element.value = element.value.substring(0, b) + text1 + element.value.substring(b, g) + text2 + element.value.substring(g);
            element.setSelectionRange(b + text1.length, g + text1.length);
            element.focus();
        } else {
            element.value += text1 + text2;
            element.focus();
        }
    }
}

/**
 * insertText
 *
 * @param f
 * @param h
 * @param e
 */
function insertText(f, h, e) {
    if (e == undefined) {
        e = "inputform"
    }
    if (document.forms[e].elements[f].createTextRange) {
        document.forms[e].elements[f].focus();
        document.selection.createRange().duplicate().text = h
    } else {
        if ((typeof document.forms[e].elements[f].selectionStart) != "undefined") {
            let a = document.forms[e].elements[f];
            let g = a.selectionEnd;
            let d = a.value.length;
            let c = a.value.substring(0, g);
            let i = a.value.substring(g, d);
            let b = a.scrollTop;
            a.value = c + h + i;
            a.selectionStart = c.length + h.length;
            a.selectionEnd = c.length + h.length;
            a.scrollTop = b;
            a.focus()
        } else {
            document.forms[e].elements[f].value += h;
            document.forms[e].elements[f].focus()
        }
    }
}

/**
 * Need documentation
 * @param a
 */
function show_hide(a) {
    document.getElementById(a).style.display = document.getElementById(a).style.display === "none" ? "block" : "none"
}

/*
letiations to show_hide, in the form of a sliding action
 */
function slide_hide(a) {
    $('#' + a).slideToggle();
}

/**
 * Need documentation
 * @param c
 * @param b
 * @returns {*|string}
 */
function getStyle(c, b) {
    if (typeof c == "string") {
        let a = document.getElementById(c)
    } else {
        let a = c
    }
    if (a.getComputedStyle()) {
        let d = a.getComputedStyle()[b];
    } else {
        if (window.getComputedStyle) {
            let d = document.defaultView.getComputedStyle(a, null).getPropertyValue(b)
        }
    }

    return d;
}

/***********************************************
 * Drop Down/ Overlapping Content- � Dynamic Drive (www.dynamicdrive.com)
 * This notice must stay intact for legal use.
 * Visit http://www.dynamicdrive.com/ for full source code
 ***********************************************/
function getposOffset(a, d) {
    let c = (d === "left") ? a.offsetLeft : a.offsetTop;
    let b = a.offsetParent;
    while (b != null) {
        if (getStyle(b, "position") !== "relative") {
            c = (d === "left") ? c + b.offsetLeft : c + b.offsetTop
        }
        b = b.offsetParent;
    }
    return c;
}

/**
 * Need documentation
 * @param e
 * @param d
 * @param a
 * @returns {boolean}
 */
function overlay(e, d, a) {
    if (document.getElementById) {
        let c = document.getElementById(d);
        c.style.display = (c.style.display !== "block") ? "block" : "none";
        let b = getposOffset(e, "left") + ((typeof a != "undefined" && a.indexOf("right") != -1) ? -(c.offsetWidth - e.offsetWidth) : 0);
        let f = getposOffset(e, "top") + ((typeof a != "undefined" && a.indexOf("bottom") != -1) ? e.offsetHeight : 0);
        c.style.left = b + "px";
        c.style.top = f + "px";
        return false
    } else {
        return true
    }
}

/**
 * Need documentation
 * @param a
 */
function overlayclose(a) {
    document.getElementById(a).style.display = "none"
}

NewWindowPopUp = null;

/**
 * Need documentation
 * @param d
 * @param c
 * @param a
 * @param b
 * @constructor
 */
function OpenWindow(d, c, a, b) {
    if (NewWindowPopUp != null) {
        NewWindowPopUp.close();
        NewWindowPopUp = null
    }
    if (b == false) {
        wtop = 0;
        wleft = 0
    } else {
        wtop = (screen.availHeight - a) / 2;
        wleft = (screen.availWidth - c) / 2
    }
    NewWindowPopUp = window.open(d, "", "toolbar=no,menubar=no,location=no,personalbar=no,scrollbars=yes,status=no,directories=no,resizable=yes,height=" + a + ",width=" + c + ",top=" + wtop + ",left=" + wleft + "");
    NewWindowPopUp.focus()
}

/**
 * Need documentation of usage and examples
 * @returns {boolean}
 */
function resize_forum_imgs() {
    let f;
    let e;
    if (self.innerWidth) {
        e = self.innerWidth
    } else {
        if (document.documentElement && document.documentElement.clientWidth) {
            e = document.documentElement.clientWidth
        } else {
            if (document.body) {
                e = document.body.clientWidth
            } else {
                e = 1000
            }
        }
    }
    if (e <= 800) {
        f = 200
    } else {
        if (e < 1152) {
            f = 300
        } else {
            if (e >= 1152) {
                f = 400
            }
        }
    }
    for (let c = 0; c < document.images.length; c++) {
        let b = document.images[c];
        if (b.className !== "forum-img") {
            continue
        }
        let j = b.height;
        let a = b.width;
        let d = false;
        if (a <= j) {
            if (j > f) {
                b.height = f;
                b.width = a * (f / j);
                d = true
            }
        } else {
            if (a > f) {
                b.width = f;
                b.height = j * (f / a);
                d = true
            }
        }
        let h = b.parentNode;
        let g = h.parentNode;
        if (h.className !== "forum-img-wrapper") {
            continue
        }
        if (d) {
            h.style.display = "inline";
            if (g.tagName !== "A") {
                h.onclick = new Function("OpenWindow('" + b.src + "', " + (a + 40) + ", " + (j + 40) + ", true)");
                h.onmouseover = "this.style.cursor='pointer'"
            }
        } else {
            h.style.display = "inline"
        }
    }
    return true
}

/**
 * Check All Form Checkboxes with a Master Checkbox
 * @param frmName - the master form element - $('#inputform');
 * @param chkName - the checkboxes element that reacts to the master checkbox
 * @param val - current state value of master checkbox
 *
 * Usage Example:
 * $('#check_all').bind('change', function(e) {
    val = $(this).is(':checked') ? 1 : 0;
    setChecked('link_table', 'link_id[]', val);
    });
 *
 */
function setChecked(frmName, chkName, val) {
    dml = document.forms[frmName];
    len = dml.elements.length;
    for (i = 0; i < len; i++) {
        if (dml.elements[i].name === chkName) {
            dml.elements[i].checked = val;
        }
    }
}

/**
 * Run time execution
 */
function onload_events() {
    resize_forum_imgs()
}

window.onload = onload_events;

function closeDiv() {
    $('#close-message').fadeTo('slow', 0.01, function () {
        $(this).slideUp('slow', function () {
            $(this).hide()
        })
    })
}

window.setTimeout('closeDiv()', 5000);

function run_admin(action, table_action, reset_table) {
    table_action = table_action || '#table_action';
    reset_table = reset_table || '#reset_table';

    $(table_action).val(action);
    $(reset_table).submit();
}

/**
 * Run this function to add showhide toggle plugin
 *Usage: showhide();
 */
let showhide = function () {
// Toggle Show
    let showhideToggle = $("a[data-toggle='show-hide']");
    if (showhideToggle.length) {
        $.each(showhideToggle, function (key, value) {
            let target = $(this).data("target"),
                targetContainer = $(this).find(target);

            $(this).append("<i class='far fa-angle-down m-l-10'></i>");

            if (targetContainer.length) {
                if (targetContainer.is('hidden')) {
                    targetContainer.show();
                } else {
                    targetContainer.hide();
                }
            }

        });
    }
    $(document).on("click", "a[data-toggle='show-hide']", function (ev) {
        ev.preventDefault();
        let target = $(this).data("target"),
            icon = $(this).find('i'),
            targetContainer = $(this).closest('.show-hide-wrapper').find(target);

        if (icon.hasClass('fa-angle-down')) {
            icon.removeClass('fa-angle-down').addClass('fa-angle-up');
        } else {
            icon.removeClass('fa-angle-up').addClass('fa-angle-down');
        }

        if (targetContainer.length) {
            if (targetContainer.is(':hidden')) {
                targetContainer.show();
            } else {
                targetContainer.hide();
            }
        }
    });
}

/**
 * Performs same as clean_request method
 * @param adds
 * @param filterArray
 * @param keep
 * @returns {string}
 */
let cleanRequest = function (adds, filterArray = [], keep = false) {
    let params = new URLSearchParams(window.location.href);
    if (filterArray.length) {
        $.each(filterArray, function (i, ckey) {
            if (params.has(ckey) && keep === false) {
                params.delete(ckey);
            }
        });
    }

    return params.toString() + '&' + adds;
};

let BASEDIR = document.location.origin + site_path;
let INFUSIONS = document.location.origin + "/infusions/";
let INCLUDES = document.location.origin + "/includes/";
let THEMES = document.location.origin + "/themes/";
let CLASSES = document.location.origin + "/includes/classes/";
