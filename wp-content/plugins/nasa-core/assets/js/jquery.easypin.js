(function ($) {
    $.fn.easypin = function (options) {

        options = options || {};

        if (localStorage) {
            localStorage.removeItem('easypin');
        }

        var parentClass = $.fn.easypin.defaults.parentClass;
        var pinMapClass = $.fn.easypin.defaults.pinMapClass;
        var hoverClass = $.fn.easypin.defaults.hoverClass;
        var dashWidth = $.fn.easypin.defaults.dashWidth;
        var imageZindex = $.fn.easypin.defaults.imageZindex;
        var pinMapZindex = $.fn.easypin.defaults.pinMapZindex;
        var hoverLayerZindex = $.fn.easypin.defaults.hoverLayerZindex;

        // set default options values and became user side
        $.extend($.fn.easypin.defaults, options);

        var willPinningElements = this;
        var loadedImgNum = 0;
        var total = $(this).length;

        // hide all images
        willPinningElements.each(function (i) {
            $(this).css('opacity', 0);
        });

        $(this).on('load', function () {

            loadedImgNum += 1;
            // show loaded image
            $(this).animate(
                {
                    'opacity': '1'
                },
                {
                    duration: 'fast',
                    easing: 'easeInQuad'
                }
            );

            if (loadedImgNum == total) {

                willPinningElements.each(function (i) {

                    // get targetimage sizes
                    var imageWidth = $(this).width();
                    var imageHeight = $(this).height();

                    if (imageHeight > 0) {

                        // create parent element and add than target image after
                        var containerElement = $(this)
                            .after(
                                $('<div/>', {'class': parentClass})
                                    .attr('data-index', setIndex(setClass(parentClass), document.body))
                            )
                            .appendTo(setClass(parentClass) + ':last')
                            .css('position', 'absolute')
                            .css('z-index', imageZindex);

                        // add class to target image
                        $(this).addClass('easypin-target');

                        // creating random key for easypin-id
                        if (!$(this).attr('data-easypin_id')) {
                            var easypinId = createRandomId();
                            $(this).attr('data-easypin_id', easypinId);
                        } else {
                            var easypinId = $(this).attr('data-easypin_id');
                        }

                        // set target image sizes to parent container
                        containerElement
                        .parent()
                        .attr($.fn.easypin.config('widthAttribute'), imageWidth)
                        .attr($.fn.easypin.config('heightAttribute'), imageHeight)
                        // and set style width, height and position
                        .css({
                            width: setPx(imageWidth),
                            height: setPx(imageHeight),
                            position: $.fn.easypin.config('parentPosition'),
                            border: setPx(dashWidth) + ' dashed #383838',
                            'box-sizing': 'content-box',
                            'webkit-box-sizing': 'content-box',
                            '-moz-box-sizing': 'content-box'
                        });

                        initPin(easypinId, $(this));
                    }
                });

                // hover event
                var parentElement = $(setClass(parentClass));

                // on hover parent element then create opacity
                $(parentElement).hover(function () {
                    // if popup is open stop hover animate
                    if (!is_open('popup', $(this))) {
                        $(this)
                        .prepend(
                            $('<div/>', {'class': hoverClass})
                            .css({
                                width: '100%',
                                height: '100%',
                                position: 'absolute',
                                opacity: 0,
                                'z-index': hoverLayerZindex,
                                'background-color': 'black',
                                cursor: 'copy'
                            })
                        )
                        // cross to children object
                        .children(setClass(hoverClass))
                        .animate({
                            opacity: 0.2
                        }, 800);
                    }

                    // on mouseleave then remove opacity
                }, function () {
                    $(this)
                    // cross to children object
                    .children(setClass(hoverClass))
                    .animate(
                        {
                            opacity: 0
                        },
                        'fast', // how fast we are animating
                        'swing', // the type of easing
                        function () {
                            $(this).remove();
                        }
                    );
                })
                .append($('<div/>', {'class': pinMapClass})
                    // child element
                    .css({
                        position: 'absolute',
                        'z-index': pinMapZindex
                    })
                )
                // set mousedown event on parent element
                .on('mousedown', function (e) {
                    // if clicked event is not parent and hover class stop propagation
                    if (!$(e.target).is(setClass(parentClass)) && !$(e.target).is(setClass(hoverClass))) {
                        e.stopPropagation();
                        return;
                    }

                    // only allow key code one
                    if (e.which != 1) {
                        return;
                    }

                    // get parent element instance
                    var parentElement = e.currentTarget;

                    // get total marker
                    var totalMarker = $('.easy-marker', parentElement).size();

                    // general limit
                    var limit = parseInt($.fn.easypin.defaults.limit);

                    // only element limit
                    var elLimit = $('img' + setClass('easypin-target'), parentElement).attr('easypin-limit');

                    // check the limit
                    if (elLimit && !isNaN(elLimit) && parseInt(elLimit) != 0) {
                        if (totalMarker >= parseInt(elLimit)) {
                            $.fn.easypin.defaults.exceeded('limit exceeded');
                            return;
                        }
                    } else if (limit != 0 && totalMarker >= limit) {
                        $.fn.easypin.defaults.exceeded('limit exceeded');
                        return;
                    }

                    // get target image sizes
                    var imageWidth = $('img' + setClass('easypin-target'), parentElement).width();
                    var imageHeight = $('img' + setClass('easypin-target'), parentElement).height();

                    // pin map class sized
                    $(setClass(pinMapClass), parentElement)
                    .css({
                        width: setPx(imageWidth),
                        height: setPx(imageHeight)
                    });

                    // get config variable
                    var src = $.fn.easypin.defaults.markerSrc;
                    var markerWidth = $.fn.easypin.defaults.markerWidth;
                    var markerHeight = $.fn.easypin.defaults.markerHeight == 'auto' ? markerWidth : $.fn.easypin.defaults.markerHeight;
                    var markerClass = $.fn.easypin.defaults.markerClass;
                    var markerContainerZindex = $.fn.easypin.defaults.markerContainerZindex;

                    // canvas border width
                    var dashWidth = $.fn.easypin.defaults.dashWidth;

                    // get x, y balance value
                    var posYBalance = $.fn.easypin.defaults.posYBalance;
                    var posXBalance = $.fn.easypin.defaults.posXBalance;

                    // get current target image instance
                    var targetImage = $('img' + setClass('easypin-target'), parentElement);

                    // set cursor position coordinate
                    var imagePositionY = targetImage.offset().top - (dashWidth - posYBalance);
                    var imagePositionX = targetImage.offset().left - (dashWidth - posXBalance);
                    var clickPosX = (e.pageX - imagePositionX);
                    var clickPosY = (e.pageY - imagePositionY);

                    // get marker half size (width/height)
                    var markerWidthHalf = (markerWidth / 2);
                    var markerHeightHalf = (markerHeight / 2);

                    // set canvas border position
                    var markerBorderX = clickPosX - (markerWidth / 2);
                    var markerBorderY = clickPosY - (markerHeight / 2);

                    if (markerBorderX < 0) {
                        markerBorderX = 0;
                    } else if (clickPosX + markerWidthHalf > imageWidth) {
                        markerBorderX = imageWidth - markerWidth;
                    }

                    if (markerBorderY < 0) {
                        markerBorderY = 0;
                    } else if (clickPosY + markerHeightHalf > imageHeight) {
                        markerBorderY = imageHeight - markerHeight;
                    }

                    var absX = markerBorderX.toFixed(3) - markerWidthHalf;
                    var absY = markerBorderY.toFixed(3) - markerHeightHalf;

                    // create tool
                    var tools = createTools({
                        markerWidth: markerWidth,
                        markerHeight: markerHeight
                    });

                    // create marker container
                    var markerContainer = createMarker({
                        tools: tools,
                        parentElement: parentElement,
                        markerClass: markerClass,
                        markerBorderX: markerBorderX,
                        markerBorderY: markerBorderY,
                        markerWidth: markerWidth,
                        markerHeight: markerHeight,
                        markerContainerZindex: markerContainerZindex,
                        markerWidth: markerWidth,
                        absX: absX,
                        absY: absY,
                        imageWidth: imageWidth,
                        imageHeight: imageHeight,
                        parentElement: parentElement,
                        src: src
                    });
                });

                // object instance add to container
                $.fn.easypin.di('instance', $.fn.easypin);

            }

        });

        return this;
    };

    var createMarker = function (depends) {

        var parentElement = depends.parentElement;

        // create marker container
        var markerContainer = $('<div/>', {'class': depends.markerClass})
                .css('left', setPx(depends.markerBorderX))
                .css('top', setPx(depends.markerBorderY - 15))
                .css('width', depends.markerWidth)
                .css('height', depends.markerHeight)
                .css('margin-left', setPx(depends.marginLeft))
                .css('margin-top', setPx(depends.marginTop))
                .css('position', 'absolute')
                .css('opacity', 0)
                .css('z-index', depends.markerContainerZindex + 10)
                //.css('background-image', 'url(' + depends.src + ')')
                //.css('background-size', setPx(depends.markerWidth))
                .css('cursor', 'move')
                .attr($.fn.easypin.config('xAttribute'), depends.absX)
                .attr($.fn.easypin.config('yAttribute'), depends.absY)
                .attr($.fn.easypin.config('widthAttribute'), depends.imageWidth)
                .attr($.fn.easypin.config('heightAttribute'), depends.imageHeight)
                .attr('data-index', setIndex('.easy-marker', depends.parentElement));
        if (depends.src !== false) {
            markerContainer.css({'background-image': 'url(' + depends.src + ')', 'background-size': setPx(depends.markerWidth)});
        }

        var markerIndex = $(markerContainer).attr('data-index');
        var parentIndex = $(depends.parentElement).attr('data-index');
        var parentId = $('.easypin-target', depends.parentElement).attr('data-easypin_id');

        // remove marker
        $(markerContainer).on('click', '.easy-delete', function (e) {

            dataRemove(parentId, markerIndex);

            $(e.currentTarget).closest('.easy-marker').remove();
            e.preventDefault();
        });

        // set the marker content
        $(markerContainer).on('click', '.easy-edit', function (e) {
            // creates popup and return instance
            var modalInstance = createPopup(e, markerContainer);

            // data set to input fields
            setDataToFields(parentId, markerIndex, modalInstance);
            e.preventDefault();
        });

        // marker tools append to marker container
        $(markerContainer).append(depends.tools);

        // set cursor x,y position
        var xPosition = depends.markerBorderX.toFixed(3);
        var yPosition = depends.markerBorderY.toFixed(3);

        // marker container append to pin parent container and run callback function
        if (is_open('popup', depends.parentElement)) {
            $(depends.parentElement).prepend(markerContainer, $.fn.easypin.defaults.drop(depends.absX, depends.absY, markerContainer));
        } else {
            $(depends.parentElement).append(markerContainer, $.fn.easypin.defaults.drop(depends.absX, depends.absY, markerContainer));
        }

        // calculate tools position for animate
        if ((depends.markerBorderY + depends.markerHeight + 10) > depends.imageHeight) {
            var toolsPosition = -13;
        } else {
            var toolsPosition = depends.markerHeight + 2;
        }

        // marker animate
        $(markerContainer).animate(
            {
                opacity: 1,
                top: setPx(depends.markerBorderY)
            },
            {
                duration: 'slow',
                easing: 'easeOutElastic',
                complete: function () {

                    // tools animate
                    $(depends.tools).animate(
                        {
                            'opacity': '.4',
                            'top': setPx(toolsPosition)
                        },
                        {
                            duration: 'slow',
                            easing: 'easeOutElastic'
                        }
                    ).hover(function () {
                        $(this).animate(
                            {
                                'opacity': '1'
                            },
                            {
                                duration: 'slow',
                                easing: 'easeInOutQuint'
                            }
                        ).css('cursor', 'pointer');
                    }, function () {
                        $(this).animate(
                            {
                                'opacity': '.4'
                            },
                            {
                                duration: 'slow',
                                easing: 'easeInOutQuint'
                            }
                        );
                    });
                }
            }
        );

        var cc = false;
        // binding methods mousedown and mousemove
        $(markerContainer).on('mousedown', function (e) {
            e.stopPropagation();

            if (e.which != 1)
                return;

            var markerInstance = e.currentTarget;

            if ($(e.target).parent().is(setClass('popoverContent'))) {
                cc = true;
            } else {
                cc = $(e.target).is(setClass('popoverContent'));
            }

            $(parentElement).on('mousemove', function (e) {

                if (
                (!$(e.target).is('div.easy-marker') &&
                !$(e.target).is(setClass($.fn.easypin.defaults.hoverClass))) &&
                cc === true) {
                    return;
                }

                var parentElement = $(markerInstance).parent();
                var markerContainer = $(markerInstance);

                var targetImage = $('img.easypin-target', parentElement);
                var markerWidthHalf = (depends.markerWidth / 2);
                var markerHeightHalf = (depends.markerHeight / 2);
                var liveY = e.pageY - targetImage.offset().top;
                var liveX = e.pageX - targetImage.offset().left;

                var relY = liveY;
                var relX = liveX;

                if (liveY - markerHeightHalf < 0) {
                    var relY = markerHeightHalf;
                } else if (liveY + markerHeightHalf > depends.imageHeight) {
                    var relY = depends.imageHeight - markerHeightHalf;
                }

                if (liveX - markerWidthHalf < 0) {
                    var relX = markerWidthHalf;
                } else if (liveX + markerWidthHalf > depends.imageWidth) {
                    var relX = depends.imageWidth - markerWidthHalf;
                }

                var absX = relX.toFixed(3) - markerWidthHalf;
                var absY = parseInt(relY.toFixed(3)) + markerHeightHalf;

                // on move marker then check tool container position
                checkToolsPosition(absY, depends.imageHeight, markerContainer);

                // drag event
                $.fn.easypin.defaults.drag(absX, absY, markerContainer);

                $(markerContainer).css({
                    position: 'absolute',
                    top: setPx(relY),
                    left: setPx(relX),
                    marginTop: -(depends.markerHeight / 2),
                    marginLeft: -(depends.markerWidth / 2)
                })
                .attr($.fn.easypin.config('xAttribute'), absX)
                .attr($.fn.easypin.config('yAttribute'), absY);
            });
        });

        // unbinding the events and removing
        $(markerContainer).on('mouseup', function (e) {
            cc = false;
            var markerInstance = e.currentTarget;
            var lat = $(markerInstance).attr($.fn.easypin.config('xAttribute'));
            var long = $(markerInstance).attr($.fn.easypin.config('yAttribute'));

            dataUpdate(parentId, markerIndex, {coords: {lat: lat, long: long}});

            $(parentElement).off('mousemove');
        });

        return markerContainer;
    };

    var createTools = function (depends) {

        var tools = $('<div/>', {'class': 'easy-tools'})
            .css({
                'width': setPx(depends.markerWidth),
                'height': '10px',
                'position': 'absolute',
                'background-color': '#868585',
                'left': '-1px',
                'top': setPx((depends.markerHeight + 2) - 5),
                'opacity': '0'

            })
            .append(function () { // edit button create
                return $('<a/>', {'class': 'easy-edit'})
                .css({
                    'display': 'inline-block',
                    'width': setPx(depends.markerWidth / 2),
                    'height': '10px',
                    'position': 'absolute',
                    'left': '0px',
                    'background-image': 'url(' + $.fn.easypin.defaults.editSrc + ')',
                    'background-repeat': 'no-repeat',
                    'background-size': '8px',
                    'background-position-y': '1px',
                    'background-position-x': '3px'
                }).hover(function () {
                    $(this)
                    .css('background-color', 'black')
                    .css('opacity', '.6');
                }, function () {
                    $(this).css('background-color', 'inherit');
                });
            })
            .append(function () { // delete button
                return $('<a/>', {'class': 'easy-delete'})
                .css({
                    'display': 'inline-block',
                    'width': setPx(depends.markerWidth / 2),
                    'height': '10px',
                    'position': 'absolute',
                    'right': '0px',
                    'background-image': 'url(' + $.fn.easypin.defaults.deleteSrc + ')',
                    'background-repeat': 'no-repeat',
                    'background-size': '8px',
                    'background-position-y': '1px',
                    'background-position-x': '3px'
                }).hover(function () {
                    $(this)
                    .css('background-color', 'black')
                    .css('opacity', '.6');
                }, function () {
                    $(this).css('background-color', 'inherit');
                });
            });

        return tools;
    };

    var checkToolsPosition = function (absY, imageHeight, markerContainer) {

        var markerHeight = $(markerContainer).height();
        var yBottom = absY + 10;

        if (yBottom > imageHeight && yBottom == imageHeight + 1) {
            $('.easy-tools', markerContainer).animate(
                {
                    'top': setPx(-13)
                },
                {
                    duration: 'slow',
                    easing: 'easeOutElastic'
                }
            );
        } else if ((absY - markerHeight) - 10 == -1) {
            $('.easy-tools', markerContainer).animate(
                {
                    'top': setPx(markerHeight + 2)
                },
                {
                    duration: 'slow',
                    easing: 'easeOutElastic'
                }
            );
        }
    };

    var getEventData = function (namespace) {

        switch (namespace) {
            case "get.coordinates":
                if (localStorage) {
                    return JSON.parse(localStorage.getItem('easypin'));
                } else {
                    try {
                        return JSON.parse(decodeURIComponent($('input[name="easypin-store"]').val()));
                    } catch (e) {
                        return null;
                    }
                }
                break;
            default:
                return null;
        }

    };

    getByIndex = function (index, data) {

        data = data || {};
        index = parseInt(index);

        if (typeof (data) == 'object') {

            var j = 0;
            for (var i in data) {

                j++;
                if (j == index) {
                    return data[i];
                }

            }

        }

        return {};

    };
    
    $.fn.easypinShow = function (options) {

        options = options || {};

        // set default options values and became user side
        $.extend($.fn.easypinShow.defaults, options);

        try {

            var depends = {
                responsive: options.responsive || false,
                pin: options.pin || 'marker.png',
                data: options.data || {},
                popover: options.popover || {},
                error: typeof (options.error) != 'function' ? function (e) {} : options.error,
                each: typeof (options.each) != 'function' ? function (i, data) {
                    return data;
                } : options.each,
                success: typeof (options.success) != 'function' ? function () {} : options.success,
                allCanvas: this
            };

            var loadedImgNum = 0;
            var total = $(this).length;

            // hide all images
            depends.allCanvas.each(function (i) {
                $(this).css('opacity', 0);
            });

            $(depends.allCanvas).one('load', function () {
                loadedImgNum += 1;

                // show loaded image
                $(this).animate(
                    {
                        'opacity': '1'
                    },
                    {
                        duration: 'fast',
                        easing: 'easeInQuad'
                    }
                );

                if (loadedImgNum == total) {
                    var markerWidth = $(this)[0].width;
                    var markerHeight = $(this)[0].height;
                    var markerImgInstance = $(this);
                    pinLocate(depends);
                    depends.success.apply();
                }
            }).each(function () {
                if (this.complete) {
                    $(this).trigger('load');
                }
            });

        } catch (e) {
            var args = new Array();
            args.push(e.message);
            args.push(e);
            depends.error.apply(null, args);
            // console.log(args);
        }

    };

    pinLocate = function (depends) {

        // for each all canvas
        $(depends.allCanvas).each(function (i) {

            var offsetTop = $(this).offset().top;
            var offsetLeft = $(this).offset().left;
            var canvas = $(this).parent();
            var height = $(this).height();
            var width = $(this).width();
            var parentWidth = $(canvas).width();

            if (depends.responsive === true) {
                var absWidth = '100%';
                var absHeight = '100%';
            } else {
                var absWidth = setPx(width);
                var absHeight = setPx(height);
            }

            var pinContainer = $('<div/>')
                .css({
                    'width': absWidth,
                    'height': absHeight,
                    'position': 'relative'
                })
                .addClass('easypin');

            $(this)
                .css('position', 'relative')
                .replaceWith(pinContainer);

            $(pinContainer).html(
                $('<div/>')
                .css('position', 'relative')
                .css('height', '100%')
                .append($(this))
            );

            var parentId = $(this).attr('data-easypin_id');

            if (typeof (depends.data) == 'string') {
                depends.data = JSON.parse(depends.data);
            }

            if (typeof (depends.data[parentId]) != 'undefined') {

                for (var j in depends.data[parentId]) {
                    if (j == 'canvas')
                        return;
                    
                    var tpl = $('#tpl-' + parentId).clone() || $('.nasa-easypin-tpl').clone();

                    // set current canvas_id and pin_id to di container
                    $.fn.easypin.di('canvas_id', parentId);
                    $.fn.easypin.di('pin_id', j);

                    // run callback function
                    var args = new Array();
                    args.push(i);
                    args.push(depends.data[parentId][j]);
                    var returnData = depends.each.apply(null, args);
                    var viewContainer = viewLocater(depends.data[parentId], j, parentWidth, createView(returnData, tpl));

                    var opacity = getCssPropertyValue('opacity', $(viewContainer).clone());

                    $(viewContainer).css('opacity', 0);
                    $(pinContainer).append(viewContainer);

                    if (depends.popover.show == true) {
                        $('.easypin-popover', pinContainer).show();
                    }

                    // marker
                    $(viewContainer).animate(
                        {
                            'opacity': opacity
                        },
                        {
                            duration: 'slow',
                            easing: 'easeOutBack'
                        }
                    );

                    // popover
                    $('.easypin-marker:last', pinContainer).on('click', function(e) {

                        if (!$(e.target).is('div.easypin-marker') && !$(e.target).parent().is('div.easypin-marker')) {
                            return;
                        }

                        // set 0 to z-index all marker
                        $('.easypin-marker', pinContainer).css('z-index', 0);

                        // set 1 to z-index current marker
                        $(this).css('z-index', 1);

                        var ins = this;
                        var clickedMarkerIndex = $(ins).index();

                        if (depends.popover.animate === true) {

                            // hide all content
                            $('.easypin-popover', pinContainer).each(function () {
                                if ($(this).css('display') == 'block' && clickedMarkerIndex != $(this).closest('.easypin-marker').index()) {
                                    $(this).toggle('fast');
                                }
                            });

                            $('.easypin-popover', ins).toggle('fast');

                        } else {
                            // hide all content
                            $('.easypin-popover', pinContainer).each(function () {
                                if ($(this).css('display') == 'block' && clickedMarkerIndex != $(this).closest('.easypin-marker').index()) {
                                    $(this).fadeOut(300);
                                }
                            });

                            if ($('.easypin-popover', this).css('display') == 'none') {
                                $('.easypin-popover', this).fadeIn(300);
                            } else {
                                $('.easypin-popover', this).fadeOut(300);
                            }
                        }
                    });
                }
            }
        });
    };

    var createView = function (data, tplInstance) {

        var popover = $('.nasa-popover-clone', tplInstance);
        var marker = $('.nasa-marker-clone', tplInstance);

        $(popover)
            .children(':first-child')
            .addClass('easypin-popover')
            .css('position', 'absolute')
            .css('display', 'none');

        $(marker)
            .children(':first-child')
            .addClass('easypin-marker')
            .css({
                'position': 'absolute'
            });
        
        var _first_child = $(marker).children(':first-child');

        var markerBorderWidth = $(_first_child).length ? $(_first_child).css('border-width').replace('px', '') : '';
        markerBorderWidth = markerBorderWidth != '' ? parseInt(markerBorderWidth) : 0;
        var markerWidth = $(marker).children(':first-child').width();
        var markerHeight = $(marker).children(':first-child').height();
        var popoverHeight = $(popover).children(':first-child').height();

        var popIns = $(popover).children(':first-child').clone();
        var bottom = getCssPropertyValue('bottom', popover);
        var newBottom = bottom == 'auto' ? setPx(markerHeight + markerBorderWidth) : bottom;

        $(popover)
            .children(':first-child')
            .css('bottom', newBottom)
            .css('cursor', 'default');

        $(marker)
            .children(':first-child')
            .append(tplHandler(data, $(popover).html()))
            .css('cursor', 'pointer');

        if (typeof data.marker_pin !== 'undefined') {
            var _html = $(marker).html();
            if (_html) {
                _html = _html.replace('{[marker_pin]}', data.marker_pin);
                $(marker).html(_html);
            }
        }

        if (typeof data.key_tab !== 'undefined') {
            var _html = $(marker).html();
            if (_html) {
                _html = _html.replace('{[key_tab]}', data.key_tab);
                $(marker).html(_html);
            }
        }

        if (typeof data.key_post !== 'undefined') {
            var _html = $(marker).html();
            if (_html) {
                _html = _html.replace('{[key_post]}', data.key_post);
                $(marker).html(_html);
            }
        }

        return $(marker).html();

    };

    var viewLocater = function (data, markerIndex, parentWidth, markerContainer) {

        var pinWidth = parseInt(data.canvas.width);
        var pinHeight = parseInt(data.canvas.height);
        var markerWidth = $(markerContainer).width();
        var markerHeight = $(markerContainer).height();

        var pos = calculatePinRate(data[markerIndex], pinWidth, markerWidth, pinHeight, markerHeight);
        var pinLeft = pos.left;
        var pinTop = pos.top;

        markerContainer = $(markerContainer)
            .css('left', pinLeft + '%')
            .css('top', pinTop + '%');

        return markerContainer;
    };

    var tplHandler = function (data, tpl) {

        if (typeof (data) == 'object') {

            var callbackVars = $.fn.easypinShow.defaults.variables;
            // console.log(data);
            for (var i in data) {

                var content = data[i];

                if (typeof (callbackVars) != 'undefined' && typeof (callbackVars[i]) == 'function') {

                    var args = new Array();

                    // current canvas id
                    args.push($.fn.easypin.container['canvas_id']);

                    // current pin id
                    args.push($.fn.easypin.container['pin_id']);
                    args.push(data[i]);
                    content = callbackVars[i].apply(null, args);

                }

                var pattern = RegExp("\\{\\[" + i + "\\]\\}", "g");
                tpl = typeof tpl !== 'undefined' && tpl !== '' ? tpl.replace(pattern, content) : tpl;
            }

            if (typeof data.title_list !== 'undefined') {
                if (data.title_list == '') {
                    tpl = '';
                }
            }
        }

        return tpl;
    };

    var calculatePinRate = function (data, pinWidth, markerWidth, pinHeight, markerHeight) {
        return {
            left: (parseInt(data.coords.lat) / pinWidth) * 100,
            top: ((parseInt(data.coords.long) - (markerHeight)) / pinHeight) * 100,
        };
    };

    var getCssPropertyValue = function (prop, el) {
        var el = $(el).hide().appendTo('body');
        var val = el.css(prop);
        el.remove();

        return val;
    };

    $.fn.easypin.clear = function () {
        if (localStorage) {
            localStorage.removeItem('easypin');
        } else {
            $('input[name="easypin-store"]').val('');
        }
    };

    $.fn.easypin.event = function (namespace, closure) {
        $.fn.easypin.di(namespace, closure);
    };

    $.fn.easypin.fire = function (namespace, params, callback) {

        if (typeof ($.fn.easypin.container[namespace]) != 'undefined') {

            if (typeof (params) == 'function') {
                callback = params;
                params = null;
            } else {
                params = params || null;
                callback = callback || null;
            }

            if (typeof (callback) == 'function') {
                var callbackArgs = new Array();
                callbackArgs.push(getEventData(namespace));
                var eventReturn = callback.apply(null, callbackArgs);
            } else {
                var eventReturn = getEventData(namespace);
            }

            var dependsArgs = new Array();
            dependsArgs.push($.fn.easypin.container['instance']);
            dependsArgs.push(eventReturn);
            dependsArgs.push(params);
            $.fn.easypin.container[namespace].apply(null, dependsArgs);

        }
    };

    $.fn.easypin.config = function (attr) {
        return $.fn.easypin.defaults[attr];
    };

    $.fn.easypin.di = function (key, depends) {
        $.fn.easypin.container[key] = depends;
    };

    $.fn.easypin.call = function (func, params) {

        params = params || '';

        var depends = func.toString().match(/function\s*\(\s*(.*?)\s*\)/i);

        if (depends.length > 1) {

            depends = depends[1];

            var clientParm = depends.replace(/(\$[a-zA-Z]+)/g, '');
            clientParm = clientParm.replace(/\s+/g, '');
            clientParm = clientParm.replace(/,+/g, ',');
            clientParm = clientParm.replace(/(^,)/, '');
            clientParm = clientParm.split(/,/g);

            expectParm = depends.match(/(\$[a-zA-Z]+)/g);

            var dependsArgs = new Array();

            for (var i in expectParm) {
                if ($.fn.easypin.container[expectParm[i]]) {
                    dependsArgs.push($.fn.easypin.container[expectParm[i]]);
                }
            }

            dependsArgs.push(params);
            func.apply(null, dependsArgs);
        }
    };

    $.fn.easypin.defaults = {
        init: {},
        limit: 0,
        popover: {},
        exceeded: function () {},
        drop: function () {},
        drag: function () {},
        modalWidth: '200px',
        widthAttribute: 'data-width',
        heightAttribute: 'data-height',
        xAttribute: 'data-x',
        yAttribute: 'data-y',
        markerSrc: 'img/marker.png',
        editSrc: 'img/edit.png',
        deleteSrc: 'img/remove.png',
        parentClass: 'pinParent',
        markerClass: 'easy-marker',
        hoverClass: 'hoverClass',
        pinMapClass: 'pinCanvas',
        parentPosition: 'relative',
        popupOpacityLayer: 'popupOpacityLayer',
        markerWidth: 32,
        markerHeight: 'auto',
        animate: false,
        posYBalance: 2,
        posXBalance: 2,
        dashWidth: 2,
        imageZindex: 1,
        pinMapZindex: 2,
        hoverLayerZindex: 3,
        markerContainerZindex: 4,
        markerBorderColor: '#FFFF00',
        downPoint: 10
    };

    $.fn.easypinShow.defaults = {};

    $.fn.easypin.container = {};
    $.fn.easypin.markerContainer = {};

    var setClass = function (name) {
        return '.' + name;
    };

    var setPx = function (num) {
        return num + 'px';
    };

    var getMarkerUrl = function () {
        return $.fn.easypin.defaults.markerSrc;
    };

    var is_open = function (type, parentElement) {
        if (type == 'popup') {
            var className = setClass($.fn.easypin.defaults.popupOpacityLayer);
            return $(className, parentElement).size() > 0;
        }
    };

    var createPopup = function (elem, markerContainer) {
        var parentElement = $(elem.target).closest('.pinParent');
        var parentIndex = $(parentElement).attr('data-index');
        var targetImage = $('.easypin-target', parentElement);
        var widthAttr = $.fn.easypin.defaults.widthAttribute;
        var heightAttr = $.fn.easypin.defaults.heightAttribute;

        // create modal base layer
        var opacityLayer = $('<div/>')
            .addClass('popupOpacityLayer')
            .css({
                'width': '100%',
                'height': '100%',
                'background-color': 'black',
                'position': 'absolute',
                'opacity': '.0',
                'z-index': 14
            });

        // append to parent container
        $(parentElement)
            .append(opacityLayer)

            // cross to child element
            .children(setClass($.fn.easypin.defaults.hoverClass))
            .hide() // hover class hide

            // back to parent element
            .parent()

            // base layer animate
            .children(setClass($.fn.easypin.defaults.popupOpacityLayer))
            .animate({
                opacity: 0.4
            }, 800);

        var width = parseInt($(parentElement).attr(widthAttr));
        var height = parseInt($(parentElement).attr(heightAttr));

        // create modal parent element
        var modalParent = $('<div/>')
            .addClass('modalParent')
            .css({
                'width': '100%',
                'height': '100%',
                'position': 'absolute',
                'z-index': 15
            })
            .on('click', function(e) {
                if ($(e.target).is('div.modalParent')) {
                    closePopup(parentElement);
                }

                e.stopPropagation();
            });

        // clonning modal content
        var modalContent = $('.easy-modal:last').clone();

        // create modal body element
        var modalContext = $('<div/>')
            .addClass('modalContext')
            .css({
                'background-color': '#fff',
                'width': $.fn.easypin.defaults.modalWidth,
                'opacity': '0',
                'position': 'absolute',
                'padding': '10px',
                '-webkit-box-shadow': '10px 13px 5px 0px rgba(0,0,0,0.75)',
                '-moz-box-shadow': '10px 13px 5px 0px rgba(0,0,0,0.75)',
                'box-shadow': '10px 13px 5px 0px rgba(0,0,0,0.75)',
                '-webkit-border-radius': '5px',
                '-moz-border-radius': '5px',
                'border-radius': '5px',
                'cursor': 'move'
            })
            .append($(modalContent).show())
            .appendTo(modalParent); // modal body append to modal parent element

        // modal parent element append to parent element
        $('.popupOpacityLayer', parentElement).after(modalParent);

        var modalHeight = $('.modalContext').height();
        var modalWidth = $(modalContext).width();

        var parentLeft = $(elem.target).closest(setClass($.fn.easypin.defaults.parentClass)).offset().left;
        var markerLeft = $(elem.target).offset().left;
        var clickPosLeft = (markerLeft - parentLeft);

        var parentTop = $(elem.target).closest(setClass($.fn.easypin.defaults.parentClass)).offset().top;
        var markerTop = $(elem.target).offset().top;
        var clickPosTop = (markerTop - parentTop);

        // modal position process
        if ($(modalContent).attr('modal-position') == 'free') {
            // calculate free left position
            if ((clickPosLeft - 100) < modalWidth) {
                var modalLeftPosition = clickPosLeft + $(markerContainer).width() + 50;
            } else {
                var modalLeftPosition = clickPosLeft - modalWidth - 100;
            }

            // calculate free top position
            if (modalHeight > height) {
                var modalTopPosition = 0;
            } else if ((height - clickPosTop) < modalHeight) {
                var modalTopPosition = height - (modalHeight + 100);
            } else {
                var modalTopPosition = clickPosTop - (modalHeight / 2);
            }
        } else {
            var modalLeftPosition = (width / 2) - (modalWidth / 2) - 10;
            var modalTopPosition = (height / 2) - ($(modalContext).height() / 2) - 10;
        }

        // modal body hide by position
        $(modalContext)
            .css('top', -(modalHeight + 5) + 'px')
            .css('left', modalLeftPosition + 'px');

        // without onhover action (close modal)
        keyBinder(27, function () {
            if (is_open('popup', parentElement)) {
                closePopup(parentElement);
                $(document.body).off('keydown');
            }
        });

        // on hover action (close modal)
        $(parentElement).hover(function () {
            if ($(this).is(':hover') && is_open('popup', parentElement)) {
                keyBinder(27, function () {
                    closePopup(parentElement);
                });
            }
        }, function () {
            $(document).off('keydown');
        });

        // animate modal body
        $(modalContext).animate(
            {
                'top': modalTopPosition + 'px',
                'opacity': '1'
            },
            {
                duration: 'slow',
                easing: 'easeOutElastic'
            }
        )
        .on('mousedown', function (e) {
            // if mouse down event is not either easy-modal, or modalContext
            // or modal form stop modal move
            if (!$(e.target).is('div.easy-modal') && !$(e.target).is('div.modalContext') && !$(e.target).is('form')) {
                e.stopPropagation();
                return;
            }

            var pinParent = $(e.currentTarget).closest('.pinParent');
            var downPageY = e.pageY - $(e.currentTarget).offset().top;
            var downPageX = e.pageX - $(e.currentTarget).offset().left;

            $(pinParent).on('mousemove', function (e) {
                $(modalContext).css({
                    position: 'absolute',
                    top: setPx((e.pageY - parentElement.offset().top) - downPageY),
                    left: setPx((e.pageX - parentElement.offset().left) - downPageX)
                });
            });
        })
        .on('mouseup', function (e) {
            var pinParent = $(e.currentTarget).closest('.pinParent');
            $(pinParent).off('mousemove');
        });
        
        $(modalContext).on('click', '.nasa_product_list_remove', function() {            
            var data_list = JSON.parse($(modalContext).find('.product_list_add[name=product_list]').val());
            var data_id = $(this).attr('data-id-product');
            var new_data_list = data_list.filter(function(item) {
                return item.product_id != data_id;
            });

            var formExists = $('form', modalContext).size() > 0;

            if (formExists) {
                var modalBody = $('form', modalContext); // form instance
            } else {
                var modalBody = $('.easy-modal', modalContext); // current modal instance
            }

            var _content = $(modalBody).find('textarea[name="content"]');
            var _product_id = $(modalBody).find('input[name="product_id"]');

            if ($(_product_id).val() == data_id) {
                $(_product_id).val('');
                $(_content).html('');
            }

            $(modalContext).find('.product_list_add[name=product_list]').val(JSON.stringify(new_data_list));
            $(this).remove();
        });

        $('.easy-submit', modalContext).on('click', function() {
            var lat = $(markerContainer).attr($.fn.easypin.defaults.xAttribute);
            var long = $(markerContainer).attr($.fn.easypin.defaults.yAttribute);
            var ImgWidth = $(markerContainer).attr($.fn.easypin.defaults.widthAttribute);
            var ImgHeight = $(markerContainer).attr($.fn.easypin.defaults.heightAttribute);
            var markerIndex = $(markerContainer).attr('data-index');
            var parentId = $('.easypin-target', parentElement).attr('data-easypin_id');

            // check the form exists
            var formExists = $('form', modalContext).size() > 0;

            if (formExists) {
                var modalBody = $('form', modalContext); // form instance
            } else {
                var modalBody = $('.easy-modal', modalContext); // current modal instance
            }

            // run callback function
            if (typeof ($.fn.easypin.defaults.done) == 'function') {
                var result = $.fn.easypin.defaults.done(modalBody);

                if (typeof (result) == 'boolean') {
                    if (result == true) {
                        closePopup(parentElement);
                    } else {
                        return;
                    }
                } else {
                    closePopup(parentElement);
                }
            }

            var formData = getFormData(modalBody, function (data) {
                data['coords'] = new Object();

                data.coords['lat'] = lat;
                data.coords['long'] = long;
                data.coords['canvas'] = new Object();
                data.coords.canvas['src'] = $(targetImage).attr('src');
                data.coords.canvas['width'] = ImgWidth;
                data.coords.canvas['height'] = ImgHeight;

                return data;
            });

            dataInsert(parentId, markerIndex, formData);

            if (!$(modalBody).hasClass('modal-mlpb')) {
                createPopover(markerContainer, formData);
            }

        });

        return modalContext;
    };

    var dataInsert = function (parentId, markerIndex, data) {
        if (localStorage) {
            storageInsert(parentId, markerIndex, data);
        } else {
            inputInsert(parentId, markerIndex, data);
        }
    };

    // local storage
    var storageInsert = function (parentId, markerIndex, data) {
        var items = localStorage.getItem('easypin');

        if (!items) {
            var items = new Object();
        } else {
            try {
                var items = JSON.parse(items);
            } catch (e) {
                var items = new Object();
            }
        }

        var items = setNestedObject(parentId, markerIndex, items);

        if (typeof (items[parentId]['canvas']) == 'undefined') {
            items[parentId]['canvas'] = data.coords.canvas;
        }

        delete data.coords.canvas;

        if (typeof (data['canvas']) != 'undefined') {
            items[parentId]['canvas'] = data.canvas;
            delete data.canvas;
        }

        items[parentId][markerIndex] = data;

        localStorage.setItem('easypin', toJsonString(items));
    };

    // stores in hidden field
    var inputInsert = function (parentId, markerIndex, data) {
        var items = $('input[name="easypin-store"]').val();

        if (!items) {
            var items = new Object();
        } else {
            try {
                var items = JSON.parse(decodeURIComponent(items));
            } catch (e) {
                var items = new Object();
            }
        }

        var items = setNestedObject(parentId, markerIndex, items);

        if (typeof (items[parentId]['canvas']) == 'undefined') {
            items[parentId]['canvas'] = data.coords.canvas;
        }

        delete data.coords.canvas;

        if (typeof (data['canvas']) != 'undefined') {
            items[parentId]['canvas'] = data.canvas;
            delete data.canvas;
        }

        items[parentId][markerIndex] = data;

        if ($('input[name="easypin-store"]').size() < 1) {
            $(setClass($.fn.easypin.defaults.parentClass) + ':first-child').before('<input type="hidden" name="easypin-store" value="' + encodeURIComponent(toJsonString(items)) + '" />');
        } else {
            $('input[name="easypin-store"]').val(encodeURIComponent(toJsonString(items)));
        }
    };

    var dataUpdate = function (parentId, markerIndex, data) {
        if (localStorage) {
            storageUpdate(parentId, markerIndex, data);
        } else {
            inputUpdate(parentId, markerIndex, data);
        }
    };

    var storageUpdate = function (parentId, markerIndex, data) {
        var items = localStorage.getItem('easypin');

        if (items) {

            try {
                var items = JSON.parse(items);
                items = setNestedObject(parentId, markerIndex, items);
                items[parentId][markerIndex] = merge(items[parentId][markerIndex], data);
                localStorage.setItem('easypin', toJsonString(items));
            } catch (e) {
                return false;
            }
        }
    };

    var inputUpdate = function (parentId, markerIndex, data) {
        var items = $('input[name="easypin-store"]').val();

        if (items) {

            try {
                var items = JSON.parse(decodeURIComponent(items));
                items = setNestedObject(parentId, markerIndex, items);
                items[parentId][markerIndex] = merge(items[parentId][markerIndex], data);
                $('input[name="easypin-store"]').val(encodeURIComponent(toJsonString(items)));
            } catch (e) {
                return false;
            }
        }
    };

    /**
     * Remove data container function
     *
     * @param parentIndex int
     * @param markerIndex int
     */
    var dataRemove = function (parentId, markerIndex) {
        if (localStorage) {
            removeFromStorage(parentId, markerIndex);
        } else {
            removeFromInput(parentId, markerIndex);
        }
    };

    /**
     * Remove data on input hidden field
     * @param  {[int]} parentIndex [parent container index]
     * @param  {[int]} markerIndex [marker container index]
     * @return {[void]}
     */
    var removeFromInput = function (parentId, markerIndex) {

        var items = $('input[name="easypin-store"]').val();
        if (items) {
            try {
                var items = JSON.parse(decodeURIComponent(items));

                items = removeHelper(parentId, markerIndex, items);

                var totalPin = $('input[name="easypin-store"]').size();
                if (totalPin < 1) {
                    $(setClass($.fn.easypin.defaults.parentClass) + ':first-child').before('<input type="hidden" name="easypin-store" value="' + encodeURIComponent(toJsonString(items)) + '" />');
                } else {
                    $('input[name="easypin-store"]').val(encodeURIComponent(toJsonString(items)));
                }

            } catch (e) {
                
            }

        }

    };

    /**
     * Remove from local storage
     *
     * @param parentIndex int
     * @param markerIndex int
     * @return void
     */
    var removeFromStorage = function (parentId, markerIndex) {
        var items = localStorage.getItem('easypin');

        if (items) {
            try {
                var items = JSON.parse(items);

                localStorage.setItem('easypin', toJsonString(removeHelper(parentId, markerIndex, items)));

            } catch (e) {
                
            }
        }
    };

    /**
     * Remove process from data object
     *
     * @param  {[int]} parentIndex [parent container index]
     * @param  {[int]} markerIndex [marker container index]
     * @param  {[object]} items       [data object]
     * @return {[object]}
     */
    var removeHelper = function (parentId, markerIndex, items) {
        if (parentId && !markerIndex) {

            if (typeof (items[parentId]) != 'undefined') {
                delete items[parentId];
            }
        }

        if (parentId && markerIndex) {
            if (typeof (items[parentId][markerIndex]) != 'undefined') {
                delete items[parentId][markerIndex];

                if (sizeof(items[parentId]) < 1 || (sizeof(items[parentId]) == 1 && typeof (items[parentId]['canvas']) != 'undefined')) {
                    delete items[parentId];
                }
            }
        }

        return items;
    };

    // set nested object
    /**
     * Set nested object
     * @param  int parentIndex parent container index
     * @param  int markerIndex marker ocntainer index
     * @param  int items       data object
     * @return object
     */
    var setNestedObject = function (parentId, markerIndex, items) {
        if (typeof (items[parentId]) == 'undefined') {
            items[parentId] = new Object();
        }

        if (typeof (items[parentId][markerIndex]) == 'undefined') {
            items[parentId][markerIndex] = new Object();
        }

        return items;
    };

    var getItem = function (parentIndex, markerIndex) {
        if (localStorage) {
            var items = localStorage.getItem('easypin');

            try {
                items = JSON.parse(items);
            } catch (e) {
                items = {};
            }
        } else {
            var items = $('input[name="easypin-store"]').val();

            try {
                items = JSON.parse(decodeURIComponent(items));
            } catch (e) {
                items = {};
            }
        }

        try {
            return items[parentIndex][markerIndex];
        } catch (e) {
            return null;
        }
    };

    // get values of current modal
    var getFormData = function (element, callback) {
        var elements = new Object();

        $('input, select, textarea', element).each(function () {

            var elementType = $(this).attr('type');
            var elementName = $(this).attr('name');

            if (elementType == 'radio') {

                var checked = $(this).filter(":checked").val();

                if (typeof (checked) != 'undefined' && typeof (elements[elementName]) == 'undefined') {
                    elements[elementName] = checked;
                }
            } else if (elementType == 'checkbox') {

                if ($(this).is(':checked')) {
                    elements[elementName] = $(this).val();
                }
            } else {
                if (typeof ($(this).val()) != 'undefined') {
                    elements[elementName] = $(this).val();
                    
                }
            }
        });

        if (typeof (callback) == 'function') {
            var args = new Array();
            args.push(elements);
            return callback.apply(null, args);
        }

        return elements;
    };

    var toJsonString = function (data) {
        return JSON.stringify(data);
    };

    // modal close
    var closePopup = function (parentElement) {
        // close opacity layer
        $(setClass($.fn.easypin.defaults.popupOpacityLayer), parentElement).animate(
            {
                opacity: 0
            },
            'fast', // how fast we are animating
            'swing', // the type of easing
            function () {
                $(this).remove();
            }
        );

        var modalHeight = $('.modalContext', parentElement).height();
        var modalWidth = $('.modalContext', parentElement).width();

        // animate modal body
        $('.modalContext', parentElement).animate(
            {
                'top': -(modalHeight + 50) + 'px',
                'opacity': '0',
                'z-index': 0
            },
            {
                duration: 'slow',
                easing: 'easeOutElastic',
                complete: function () {
                    $('.modalParent', parentElement).remove();
                }
            }
        );
    };

    var keyBinder = function (expectCode, callback) {
        $(document).on('keydown', function (e) {
            if (e.which == expectCode) {
                callback.apply(null);
                $(this).off('keydown');
            }
        });
    };

    var setIndex = function (selector, parent) {
        var index = $(selector + ':last', parent).attr('data-index');

        if (typeof (index) == 'undefined') {
            return '0';
        }

        return parseInt(index) + 1;
    };

    var sizeof = function (data) {
        if (typeof (data) == 'object') {
            var j = 0;

            for (var i in data) {
                j++;
            }

            return j;
        }
    };

    /**
     * Set to input fields all data
     * @param  int parentIndex   parent container index
     * @param  int markerIndex   marker container index
     * @param  object modalInstance current modal instance
     * @return void
     */
    var setDataToFields = function (parentId, markerIndex, modalInstance) {
        var item = getItem(parentId, markerIndex);

        if (typeof (item) == 'object') {

            for (var i in item) {

                var element = $('[name="' + i + '"]', modalInstance);
                var type = $(element).prop('type');

                if (i== 'product_list') {
                    var _mlpb_modal = $(modalInstance).find('.easy-modal.modal-mlpb')
                    var html = '';
                    var svg ='<svg height="20" width="20" viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>';
                    if ($(_mlpb_modal).length) {
                        if (item[i] != '') {
                            data_list = JSON.parse(item[i]);
                            data_list.forEach(function(item) {
                                html += '<a class="nasa_product_list_remove" href="javascript:void(0);" data-id-product="' + item.product_id + '">' + item.content + svg + '</a>';
                            });

                            $(_mlpb_modal).find('.nasa_pin_mlpb_product_list').html(html);
                        }
                    }
                }

                if (type == 'text' || type == 'hidden') {
                    $(element).attr('value', item[i]);
                } else if (type == 'checkbox') {
                    $(element).attr('checked', true);
                } else if (type == 'radio') {
                    $('[value="' + item[i] + '"]', modalInstance).attr('checked', true);
                } else if (type == 'textarea') {
                    $(element).val(item[i]);
                } else if (type == 'select-one') {
                    $(element).val(item[i]).prop('selected', true);
                }

            }

        }
    };

    var createPopover = function (markerContainer, formData) {

        // check and set popover callbacks
        var popoverCallBacks = sizeof($.fn.easypin.defaults.popover) > 0 ? $.fn.easypin.defaults.popover : false;

        var arrow = $('<div/>')
            .addClass('popover-arrow-down')
            .css({
                width: 0,
                height: 0,
                'border-left': '10px solid transparent',
                'border-right': '10px solid transparent',
                'border-top': '10px solid #000',
                'opacity': '.8',
                position: 'absolute',
                bottom: '-50px',
                left: '58px'
            });

        var tooltipContainer = $('<div/>')
            .addClass('popover')
            .css('position', 'absolute')
            .css('display', 'inline')
            .css('top', '-51px')
            .css('left', '-51px')
            .css('opacity', '0');

        var popoverHtml = $('[popover]:last').clone();
        popoverHtml.removeAttr('popover');
        var toHtml = popoverHtml.html();
        var popoverUserWidth = popoverHtml.attr('width') ? popoverHtml.attr('width') : 150;

        var defaultStyle = {
            color: '#FFFFFF',
            background: '#000000',
            opacity: '.8',
            height: 'auto',
            'line-height': '40px',
            'border-radius': '5px',
            cursor: 'context-menu'
        };

        if (typeof ($.fn.easypin.defaults.popoverStyle) == 'object') {
            delete $.fn.easypin.defaults.popoverStyle.width;
            delete $.fn.easypin.defaults.popoverStyle.position;
            popoverStyle = merge(defaultStyle, $.fn.easypin.defaults.popoverStyle);
        } else {
            popoverStyle = defaultStyle;
        }

        var span = $('<span/>')
            .addClass('popoverContent')
            .css({
                position: 'absolute',
                width: setPx(popoverUserWidth)
            })
            .css(popoverStyle);

        var bgColor = $(span).css('background-color');

        $(arrow).css('border-top-color', bgColor);

        if (popoverHtml.attr('shadow') == 'true') {
            $(span)
            .css({
                '-webkit-box-shadow': '10px 13px 5px 0px rgba(0,0,0,0.75)',
                '-moz-box-shadow': '10px 13px 5px 0px rgba(0,0,0,0.75)',
                'box-shadow': '10px 13px 5px 0px rgba(0,0,0,0.75)'
            });
        }

        // delete canvas parameters
        delete formData['canvas'];

        for (var i in formData) {

            // callback check and run
            if (popoverCallBacks && typeof (popoverCallBacks[i]) == 'function') {
                var args = new Array();
                args.push(formData[i]);
                formData[i] = popoverCallBacks[i].apply(null, args);
            }

            var pattern = RegExp("\\{\\[" + i + "\\]\\}", "g");
            toHtml = toHtml.replace(pattern, formData[i]);

        }

        $(span).append(toHtml);
        $(tooltipContainer).append(span).append(arrow);

        // remove previous popover
        if ($('div.popover', markerContainer).size() > 0) {
            // animate modal body
            $('div.popover', markerContainer).animate(
                {
                    'top': '-' + setPx((popoverHeight - 15)),
                    'opacity': '0'
                },
                {
                    duration: 'slow',
                    easing: 'easeOutElastic',
                    complete: function () {
                        $(this).remove();
                    }
                }
            );
        }

        // popover element apend yo marker container
        $(markerContainer).prepend(tooltipContainer);

        var popoverEl = $('div.popover > span:first-child', markerContainer);
        var popoverHeight = popoverEl.height();
        var popoverWidth = popoverEl.width();

        // set popover container position
        $(tooltipContainer)
            .css('top', '-' + setPx((popoverHeight + 12) - 10))
            .css('left', '-' + setPx((popoverWidth / 2) - 16));

        // set arrow position
        $(arrow, tooltipContainer)
            .css('left', setPx((popoverWidth / 2) - 10))
            .css('top', setPx(popoverHeight));

        // animate modal body
        $(tooltipContainer).animate(
            {
                'top': '-' + setPx((popoverHeight + 12)),
                'opacity': '1'
            },
            {
                duration: 'slow',
                easing: 'easeOutElastic'
            }
        );
    };

    var initPin = function (imgIndex, element) {

        var initData = $.fn.easypin.defaults.init;
        var config = getConfigs();

        if (typeof (initData) == 'string') {
            initData = JSON.parse(initData);
        }

        var parentElement = $(element).parents(setClass(config.parentClass));

        if (sizeof(initData) > 0 && $(element).attr('easypin-init') != 'false') {

            // canvas border width
            var dashWidth = $.fn.easypin.defaults.dashWidth;

            // get x, y balance value
            var posYBalance = config.posYBalance;
            var posXBalance = config.posXBalance;

            // get current target image instance
            var targetImage = $('img' + setClass('easypin-target'), parentElement);

            if (typeof (initData[imgIndex]) == 'undefined') {
                return;
            }

            for (var i in initData[imgIndex]) {

                if (isNaN(i) === true)
                    return;

                var imageWidth = parseInt(initData[imgIndex].canvas.width);
                var imageHeight = parseInt(initData[imgIndex].canvas.height);
                var lat = parseInt(initData[imgIndex][i].coords.lat);
                var long = parseInt(initData[imgIndex][i].coords.long);
                // set cursor position coordinate
                var imagePositionY = targetImage.offset().top - (config.dashWidth - posYBalance);
                var imagePositionX = targetImage.offset().left - (config.dashWidth - posXBalance);

                var clickPosX = lat;//(lat-imagePositionX);
                var clickPosY = long;//(long-imagePositionY);
                // get marker half size (width/height)
                var markerWidthHalf = (config.markerWidth / 2);
                var markerHeightHalf = (config.markerHeight / 2);

                // set canvas border position
                var markerBorderX = clickPosX - (config.markerWidth / 2);
                var markerBorderY = clickPosY - (config.markerHeight / 2);

                if (markerBorderX < 0) {
                    markerBorderX = 0;
                } else if (clickPosX + markerWidthHalf > imageWidth) {
                    markerBorderX = imageWidth - config.markerWidth;
                }

                if (markerBorderY < 0) {
                    markerBorderY = 0;
                } else if (clickPosY + markerHeightHalf > imageHeight) {
                    markerBorderY = imageHeight - config.markerHeight;
                }

                var absX = markerBorderX.toFixed(3) - markerWidthHalf;
                var absY = markerBorderY.toFixed(3) - markerHeightHalf;

                // create tool
                var tools = createTools({
                    markerWidth: config.markerWidth,
                    markerHeight: config.markerHeight
                });

                // create marker container
                var markerContainer = createMarker({
                    tools: tools,
                    parentElement: parentElement,
                    markerClass: config.markerClass,
                    markerBorderX: markerBorderX,
                    markerBorderY: markerBorderY,
                    marginLeft: (config.markerWidth / 2),
                    marginTop: -(config.markerHeight / 2),
                    markerWidth: config.markerWidth,
                    markerHeight: config.markerHeight,
                    markerContainerZindex: config.markerContainerZindex,
                    absX: absX,
                    absY: absY,
                    imageWidth: imageWidth,
                    imageHeight: imageHeight,
                    parentElement: parentElement,
                    src: config.src
                });

                var markerIndex = $(markerContainer).attr('data-index');
                var markerData = merge(initData[imgIndex][i], {'canvas': initData[imgIndex]['canvas']});

                dataInsert(imgIndex, markerIndex, markerData);

                if (!$(element).hasClass('nasa_pin_mlpb_image')) {
                    createPopover(markerContainer, initData[imgIndex][i]);
                }
                
            }
        }
    };

    var createRandomId = function () {
        var length = 10;
        var strings = 'abcdefghijklmnoprstuvyzABCDEFGHIJKLMNOPRSTUVYZ1234567890_';

        var key = '';

        for (var i = 1; i <= length; i++) {

            var randNum = Math.floor(Math.random() * (strings.length - 1 + 1) + 1);

            if (typeof (strings[randNum]) != 'undefined') {
                key += strings[randNum];
            }

        }

        return key;
    };

    var getConfigs = function () {

        return {
            src: $.fn.easypin.defaults.markerSrc,
            markerWidth: $.fn.easypin.defaults.markerWidth,
            markerHeight: $.fn.easypin.defaults.markerHeight == 'auto' ? $.fn.easypin.defaults.markerWidth : $.fn.easypin.defaults.markerHeight,
            markerClass: $.fn.easypin.defaults.markerClass,
            parentClass: $.fn.easypin.defaults.parentClass,
            markerContainerZindex: $.fn.easypin.defaults.markerContainerZindex,
            // canvas border width
            dashWidth: $.fn.easypin.defaults.dashWidth,
            // get x, y balance value
            posYBalance: $.fn.easypin.defaults.posYBalance,
            posXBalance: $.fn.easypin.defaults.posXBalance,
            // canvas border width
            dashWidth: $.fn.easypin.defaults.dashWidth
        };
    };

    /*
     * Recursively merge properties of two objects
     */
    function merge(obj1, obj2) {

        for (var p in obj2) {
            try {
                obj1[p] = obj2[p];

            } catch (e) {
                // Property in destination object not set; create it and set its value.
                obj1[p] = obj2[p];

            }
        }

        return obj1;
    }
}(jQuery));

/**
 * Document nasa-core ready
 */
jQuery(document).ready(function($) {
    "use strict";
    /**
     * Pin init
     */
    load_pin_products_banner($);
    load_pin_material_banner($);
    load_pin_multi_products_banner($);
    $('body').on('click', '.easypin-marker .nasa-marker-icon-wrap', function() {
        var _this = $(this);
        var _act = $(_this).parents('.easypin-marker').hasClass('nasa-active');
        var _wrap = $(_this).parents('.nasa-pin-wrap');
        
        if (!_act) {
            $(_wrap).find('.easypin-marker').removeClass('nasa-active');
            $(_this).parents('.easypin-marker').addClass('nasa-active');
        }

        if ($(_this).hasClass('nasa-marker-icon-multi-product')) {
            var key = $(_this).attr('data-key-tab');
            var _product_tab = $(_wrap).find('.nasa_multi_product_pin_tab_wrap .nasa_multi_product_pin_tab[data-key-tab="'+key+'"]')
            var _device = $('body').attr('data-elementor-device-mode');

            if ($(_product_tab)) {
                if(!$(_product_tab).hasClass('current-tab')) {
                    var _curent_Tab = $(_product_tab).siblings('.current-tab');

                    $(_curent_Tab).addClass('current-tab-hiding').removeClass('current-tab');
                    $(_product_tab).addClass('nasa-active');

                    setTimeout(function() {
                        $(_curent_Tab).removeClass('current-tab-hiding');
                        $(_product_tab).removeClass('nasa-active').addClass('current-tab');
                    },20);
                }

                if (_device == 'mobile') {
                    $('html, body').animate({
                        scrollTop: $(_product_tab).offset().top - 200
                    }, 500);
                }
            }
        }

        if ($(_this).hasClass('nasa-marker-icon-product-slide')) {
            var key = $(_this).attr('data-key-post');
            var _product_slide = $(_wrap).find('.nasa_product_pin_slide_wrap .nasa-slick-slider')
            var _device = $('body').attr('data-elementor-device-mode');
            var _data_count_pin = $(_wrap).find('.nasa_product_pin_slide_wrap').attr('data-count-pin');

            if ($(_product_slide)) {

                if (key == _data_count_pin) {
                    key = key -1;
                }

                $(_product_slide).slick('slickGoTo',key);

                if (_device == 'mobile') {
                    $('html, body').animate({
                        scrollTop: $(_product_slide).offset().top - 200
                    }, 500);
                }
            }
        }
    });


    $('body').on('mouseenter', '.easypin-marker .nasa-marker-icon-wrap', function() {
        var _device = $('body').attr('data-elementor-device-mode');
        if (!$(this).parents('.easypin-marker').hasClass('nasa-active') && _device != 'mobile') {
            $(this).trigger('click');
        }
    });


    
    $('body').on('nasa_inited_slick', function() {
        /**
         * Pin in slick
         */
        load_pin_products_banner($);
        load_pin_material_banner($);
        load_pin_multi_products_banner($);
    });
    
    /**
     * After Load ajax
     */
    $('body').on('nasa_after_ajax_funcs', function() {
        /**
         * Pin in slick
         */
        load_pin_products_banner($);
        load_pin_material_banner($);
        load_pin_multi_products_banner($);
    });
    
    /**
     * init pin
     * 
     * @param {type} $
     * @returns {undefined}
     */
    $('body').on('nasa_init_pins_banners', function() {
        load_pin_products_banner($);
        load_pin_material_banner($);
        load_pin_multi_products_banner($);
    });
    
    $('body').on('nasa_rendered_template', function() {
        load_pin_products_banner($);
        load_pin_material_banner($);
        load_pin_multi_products_banner($);
    });
});

/**
 * Pin Product banner
 * 
 * @param {type} $
 * @returns {undefined}
 */
function load_pin_products_banner($) {
    if ($('.nasa-pin-banner-wrap').length > 0) {
        /**
         * Trigger before pin
         */
        $('body').trigger('nasa_before_pin_banners');
        
        $('.nasa-pin-banner-wrap').each(function() {
            var _this = $(this);
            if (!$(_this).hasClass('nasa-inited')) {
                var _wrap_img = $(_this).find('.nasa-wrap-relative-image');
                var _img_div = $(_wrap_img).find('.nasa_pin_pb_image');
                
                var _height = $(_img_div).attr('data-height');
                var _width = $(_img_div).attr('data-width');
                var _src = $(_img_div).attr('data-src');
                var _easypin_id = $(_img_div).attr('data-easypin_id');
                var _alt = $(_img_div).attr('data-alt');
                
                if (_src) {
                    $(_img_div).replaceWith('<img height="' + _height + '" width="' + _width + '" src="' + _src + '" data-easypin_id="' + _easypin_id + '" alt="' + _alt + '" class="nasa_pin_pb_image" />');

                    var _img = $(_this).find('img.nasa_pin_pb_image');
                    var _init = JSON.parse($(_this).attr('data-pin'));

                    if (_init && $(_img).length > 0) {
                        $(_img).easypinShow({
                            data: _init,
                            responsive: true,
                            popover: {
                                show: true,
                                animate: false
                            },
                            each: function(index, data) {
                                return data;
                            },
                            error: function(e) {
                                console.log(e);
                            },
                            success: function() {
                                if ($(_this).find('.nasa-product-pin .price.nasa-price-pin').length > 0){
                                    $(_this).find('.nasa-product-pin .price.nasa-price-pin').each(function() {
                                        var _pid = $(this).attr('data-product_id');
                                        if (parseInt(_pid) && $(_this).find('.nasa-price-pin-' + _pid).length > 0) {
                                            $(this).html($(_this).find('.nasa-price-pin-' + _pid).html());
                                        }
                                    });
                                }

                                if ($(_this).hasClass('nasa-has-effect')) {
                                    $(_this).find('.nasa-marker-icon-wrap').addClass('nasa-effect');
                                }

                                $(_this).find('.nasa-easypin-tpl').remove();

                                $(_this).addClass('nasa-inited');

                                if ($('#nasa-single-product-ajax').length) {
                                    $('.pin-product-url').addClass('nasa-ajax-call');
                                }
                            }
                        });
                    }

                    $('body').on('click', _img, function() {
                        $(_this).find('.easypin-popover').hide();
                    });

                    $(document).on('keyup', function(e) {
                        if (e.keyCode === 27){
                            $(_img).trigger('click');
                        }
                    });
                }
            }
        });
    }
}

/**
 * Pin Material Banner
 * 
 * @param {type} $
 * @returns {undefined}
 */
function load_pin_material_banner($) {
    if ($('.nasa-pin-material-banner-wrap').length > 0) {
        /**
         * Trigger before pin
         */
        $('body').trigger('nasa_before_pin_banners');
        
        $('.nasa-pin-material-banner-wrap').each(function() {
            var _this = $(this);
            if (!$(_this).hasClass('nasa-inited')) {
                var _wrap_img = $(_this).find('.nasa-wrap-relative-image');
                var _img_div = $(_wrap_img).find('.nasa_pin_mb_image');
                
                var _height = $(_img_div).attr('data-height');
                var _width = $(_img_div).attr('data-width');
                var _src = $(_img_div).attr('data-src');
                var _easypin_id = $(_img_div).attr('data-easypin_id');
                var _alt = $(_img_div).attr('data-alt');
                
                if (_src) {
                
                    $(_img_div).replaceWith('<img height="' + _height + '" width="' + _width + '" src="' + _src + '" data-easypin_id="' + _easypin_id + '" alt="' + _alt + '" class="nasa_pin_mb_image" />');

                    var _img = $(_this).find('img.nasa_pin_mb_image');
                    var _init = JSON.parse($(_this).attr('data-pin'));

                    if (_init && $(_img).length > 0) {
                        $(_img).easypinShow({
                            data: _init,
                            responsive: true,
                            popover: {
                                show: false,
                                animate: false
                            },
                            each: function(index, data) {
                                return data;
                            },
                            error: function(e) {
                                console.log(e);
                            },
                            success: function() {
                                if ($(_this).hasClass('nasa-has-effect')) {
                                    $(_this).find('.nasa-marker-icon-wrap').addClass('nasa-effect');
                                }

                                $(_this).find('.nasa-easypin-tpl').remove();

                                $(_this).addClass('nasa-inited');
                            }
                        });
                    }

                    $('body').on('click', _img, function() {
                        $(_this).find('.easypin-popover').hide();
                    });

                    $(document).on('keyup', function(e) {
                        if (e.keyCode === 27){
                            $(_img).trigger('click');
                        }
                    });
                }
            }
        });
    }
}

function load_pin_multi_products_banner($) {
    if ($('.nasa-pin-banner-wrap').length > 0) {
        /**
         * Trigger before pin
         */
        $('body').trigger('nasa_before_pin_banners');
        
        $('.nasa-pin-banner-wrap').each(function() {
            var _this = $(this);
            if (!$(_this).hasClass('nasa-inited')) {
                var _wrap_img = $(_this).find('.nasa-wrap-relative-image');
                var _img_div = $(_wrap_img).find('.nasa_pin_mlpb_image');
                
                var _height = $(_img_div).attr('data-height');
                var _width = $(_img_div).attr('data-width');
                var _src = $(_img_div).attr('data-src');
                var _easypin_id = $(_img_div).attr('data-easypin_id');
                var _alt = $(_img_div).attr('data-alt');
                
                if (_src) {
                    $(_img_div).replaceWith('<img height="' + _height + '" width="' + _width + '" src="' + _src + '" data-easypin_id="' + _easypin_id + '" alt="' + _alt + '" class="nasa_pin_mlpb_image" />');

                    var _img = $(_this).find('img.nasa_pin_mlpb_image');
                    var _init = JSON.parse($(_this).attr('data-pin'));

                    if (_init && $(_img).length > 0) {
                        $(_img).easypinShow({
                            data: _init,
                            responsive: true,
                            popover: {
                                show: true,
                                animate: false
                            },
                            each: function(index, data) {
                                return data;
                            },
                            error: function(e) {
                                console.log(e);
                            },
                            success: function() {
                                if ($(_this).find('.nasa-product-pin .price.nasa-price-pin').length > 0){
                                    $(_this).find('.nasa-product-pin .price.nasa-price-pin').each(function() {
                                        var _pid = $(this).attr('data-product_id');
                                        if (parseInt(_pid) && $(_this).find('.nasa-price-pin-' + _pid).length > 0) {
                                            $(this).html($(_this).find('.nasa-price-pin-' + _pid).html());
                                        }
                                    });
                                }

                                if ($(_this).hasClass('nasa-has-effect')) {
                                    $(_this).find('.nasa-marker-icon-wrap').addClass('nasa-effect');
                                }

                                $(_this).find('.nasa-easypin-tpl').remove();

                                $(_this).addClass('nasa-inited');

                                if ($('#nasa-single-product-ajax').length) {
                                    $('.pin-product-url').addClass('nasa-ajax-call');
                                }
                            }
                        });
                    }

                    $('body').on('click', _img, function() {
                        $(_this).find('.easypin-popover').hide();
                    });

                    $(document).on('keyup', function(e) {
                        if (e.keyCode === 27){
                            $(_img).trigger('click');
                        }
                    });
                }
            }
        });
    }
}
