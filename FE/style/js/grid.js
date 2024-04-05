/* ======================================================= 
 * Auto Albums - Multi Level Responsive Grid
 * By David Blanco
 *
 * Contact: http://codecanyon.net/user/davidbo90
 *
 * Created: July 23, 2013
 *
 * Copyright (c) 2013, David Blanco. All rights reserved.
 * Released under CodeCanyon License http://codecanyon.net/
 *
 * Note: Script based in jQuery Masonry v2.1.07 made by David DeSandro http://masonry.desandro.com/ (under MIT)
 *
 * ======================================================= */

(function(d) {
    var f, c, k, g = 30, j = 75, i = 10, l = 1000;
    if ("ontouchend" in document) {
        f = "touchend.cj_swp";
        c = "touchmove.cj_swp";
        k = "touchstart.cj_swp"
    } else {
        f = "mouseup.cj_swp";
        c = "mousemove.cj_swp";
        k = "mousedown.cj_swp"
    }
    d.fn.touchSwipe = function(n, o) {
        if (o) {
            this.data("stopPropagation", true)
        }
        if (n) {
            return this.each(m, [n])
        }
    };
    d.fn.touchSwipeLeft = function(n, o) {
        if (o) {
            this.data("stopPropagation", true)
        }
        if (n) {
            return this.each(e, [n])
        }
    };
    d.fn.touchSwipeRight = function(n, o) {
        if (o) {
            this.data("stopPropagation", true)
        }
        if (n) {
            return this.each(b, [n])
        }
    };
    function m(n) {
        d(this).touchSwipeLeft(n).touchSwipeRight(n)
    }
    function e(n) {
        var o = d(this);
        if (!o.data("swipeLeft")) {
            o.data("swipeLeft", n)
        }
        if (!o.data("swipeRight")) {
            h(o)
        }
    }
    function b(n) {
        var o = d(this);
        if (!o.data("swipeRight")) {
            o.data("swipeRight", n)
        }
        if (!o.data("swipeLeft")) {
            h(o)
        }
    }
    d.fn.unbindSwipeLeft = function() {
        this.removeData("swipeLeft");
        if (!this.data("swipeRight")) {
            this.unbindSwipe(true)
        }
    };
    d.fn.unbindSwipeRight = function() {
        this.removeData("swipeRight");
        if (!this.data("swipeLeft")) {
            this.unbindSwipe(true)
        }
    };
    d.fn.unbindSwipe = function(n) {
        if (!n) {
            this.removeData("swipeLeft swipeRight stopPropagation")
        }
        return this.unbind(k + " " + c + " " + f)
    };
    function h(n) {
        n.unbindSwipe(true).bind(k, a)
    }
    function a(n) {
        var o = new Date().getTime(), r = n.originalEvent.touches ? n.originalEvent.touches[0] : n, u = d(this).bind(c, s).one(f, x), q = r.pageX, p = r.pageY, v, t, w;
        if (u.data("stopPropagation")) {
            n.stopImmediatePropagation()
        }
        function x(y) {
            u.unbind(c);
            if (o && w) {
                if (w - o < l && Math.abs(q - v) > g && Math.abs(p - t) < j) {
                    if (q > v) {
                        if (u.data("swipeLeft")) {
                            u.data("swipeLeft")("left")
                        }
                    } else {
                        if (u.data("swipeRight")) {
                            u.data("swipeRight")("right")
                        }
                    }
                }
            }
            o = w = null
        }
        function s(y) {
            if (o) {
                r = y.originalEvent.touches ? y.originalEvent.touches[0] : y;
                w = new Date().getTime();
                v = r.pageX;
                t = r.pageY;
                if (Math.abs(q - v) > i) {
                    y.preventDefault()
                }
            }
        }}
}
)(jQuery);
(function(e, f, g) {
    var b = f.event, a = f.event.handle ? "handle" : "dispatch", d;
    b.special.smartresize = {setup: function() {
            f(this).bind("resize", b.special.smartresize.handler)
        }, teardown: function() {
            f(this).unbind("resize", b.special.smartresize.handler)
        }, handler: function(k, h) {
            var j = this, i = arguments;
            k.type = "smartresize";
            if (d) {
                clearTimeout(d)
            }
            d = setTimeout(function() {
                b[a].apply(j, i)
            }, h === "execAsap" ? 0 : 100)
        }};
    f.fn.smartresize = function(h) {
        return h ? this.bind("smartresize", h) : this.trigger("smartresize", ["execAsap"])
    };
    f.Mason = function(h, i) {
        this.element = f(i);
        this._create(h);
        this._init()
    };
    f.Mason.settings = {isResizable: true, isAnimated: false, animationOptions: {queue: false, duration: 500, complete: function() {
                var h = f(this);
                if (!h.hasClass("grid-brick")) {
                    if (f.fn.grid.defaults.lazyLoad) {
                        f.waypoints("refresh")
                    }
                    if (h.hasClass("grid-with-loading-boxes")) {
                        h.removeClass("grid-with-loading-boxes");
                        h.addClass("completeAddingImages")
                    }
                }
            }}, gutterWidth: 0, isRTL: false, isFitWidth: false, containerStyle: {position: "relative"}};
    f.Mason.prototype = {_resized: false, _filterFindBricks: function(i) {
            var h = this.options.itemSelector;
            return !h ? i : i.filter(h).add(i.find(h))
        }, _getBricks: function(i) {
            var h = this._filterFindBricks(i).css({position: "absolute"}).addClass("grid-brick");
            return h
        }, _create: function(k) {
            this.options = f.extend(true, {}, f.Mason.settings, k);
            this.styleQueue = [];
            var j = this.element[0].style;
            this.originalStyle = {height: j.height || ""};
            var l = this.options.containerStyle;
            for (var n in l) {
                this.originalStyle[n] = j[n] || ""
            }
            this.element.css(l);
            this.horizontalDirection = this.options.isRTL ? "right" : "left";
            var i = this.element.css("padding-" + this.horizontalDirection);
            var m = this.element.css("padding-top");
            this.offset = {x: i ? parseInt(i, 10) : 0, y: m ? parseInt(m, 10) : 0};
            this.isFluid = this.options.columnWidth && typeof this.options.columnWidth === "function";
            var h = this;
            setTimeout(function() {
                h.element.addClass("grid")
            }, 0);
            if (this.options.isResizable) {
                f(e).bind("smartresize.grid", function() {
                    h.resize()
                })
            }
            this.reloadItems()
        }, _init: function(h) {
            this._getColumns();
            this._reLayout(h)
        }, option: function(h, i) {
            if (f.isPlainObject(h)) {
                this.options = f.extend(true, this.options, h)
            }
        }, layout: function(s, w) {
            for (var p = 0, q = s.length; p < q; p++) {
                this._placeBrick(s[p])
            }
            var h = {};
            h.height = Math.max.apply(Math, this.colYs);
            if (this.options.isFitWidth) {
                var n = 0;
                p = this.cols;
                while (--p) {
                    if (this.colYs[p] !== 0) {
                        break
                    }
                    n++
                }
                h.width = (this.cols - n) * this.columnWidth - this.options.gutterWidth
            }
            this.styleQueue.push({$el: this.element, style: h});
            var j = !this.isLaidOut ? "css" : (this.options.isAnimated ? "animate" : "css"), m = this.options.animationOptions;
            var o;
            var l = false;
            for (p = 0, q = this.styleQueue.length; p < q; p++) {
                o = this.styleQueue[p];
                j = !this.isLaidOut ? "css" : (this.options.isAnimated ? "animate" : "css");
                var r = o.$el.hasClass("loading-box");
                if (r && !this.element.hasClass("noSupportTransform")) {
                    j = "css";
                    l = true
                } else {
                    if (l) {
                        o.$el.css(o.style, m)
                    }
                }
                o.$el.removeClass("loading-box");
                var k = o.$el.css("left");
                var u = o.$el.css("top");
                var v = o.style.left + "px";
                var t = o.style.top + "px";
                if ((k == v && u == t) || r == true) {
                    o.$el.data("moving", false)
                } else {
                    o.$el.data("moving", true)
                }
                o.$el[j](o.style, m)
            }
            this.styleQueue = [];
            if (w) {
                w.call(s)
            }
            this.isLaidOut = true
        }, _getColumns: function() {
            var h = this.options.isFitWidth ? this.element.parent() : this.element, i = h.width();
            this.columnWidth = this.isFluid ? this.options.columnWidth(i) : this.options.columnWidth || this.$bricks.outerWidth(true) || i;
            this.columnWidth += this.options.gutterWidth;
            this.cols = Math.floor((i + this.options.gutterWidth) / this.columnWidth);
            this.cols = Math.max(this.cols, 1)
        }, _placeBrick: function(q) {
            var o = f(q), s, w, l, u, m;
            s = Math.ceil(o.outerWidth(true) / this.columnWidth);
            s = Math.min(s, this.cols);
            if (s === 1) {
                l = this.colYs
            } else {
                w = this.cols + 1 - s;
                l = [];
                for (m = 0; m < w; m++) {
                    u = this.colYs.slice(m, m + s);
                    l[m] = Math.max.apply(Math, u)
                }
            }
            var h = Math.min.apply(Math, l), v = 0;
            for (var n = 0, r = l.length; n < r; n++) {
                if (l[n] === h) {
                    v = n;
                    break
                }
            }
            var p = {top: h + this.offset.y};
            p[this.horizontalDirection] = this.columnWidth * v + this.offset.x;
            this.styleQueue.push({$el: o, style: p});
            var t = h + o.outerHeight(true), k = this.cols + 1 - r;
            for (n = 0; n < k; n++) {
                this.colYs[v + n] = t
            }
        }, resize: function() {
            var h = this.cols;
            this._getColumns();
            if (this.isFluid || this.cols !== h) {
                this._reLayout()
            }
        }, _reLayout: function(j) {
            var h = this.cols;
            this.colYs = [];
            while (h--) {
                this.colYs.push(0)
            }
            this.layout(this.$bricks, j)
        }, reloadItems: function() {
            this.$bricks = this._getBricks(this.element.children())
        }, reload: function(h) {
            this.reloadItems();
            this._init(h)
        }, appended: function(i, j, k) {
            if (j) {
                this._filterFindBricks(i).css({top: this.element.height()});
                var h = this;
                setTimeout(function() {
                    h._appended(i, k)
                }, 1)
            } else {
                this._appended(i, k)
            }
        }, _appended: function(h, j) {
            var i = this._getBricks(h);
            this.$bricks = this.$bricks.add(i);
            this.layout(i, j)
        }, remove: function(h) {
            this.$bricks = this.$bricks.not(h);
            h.remove()
        }, destroy: function() {
            this.$bricks.removeClass("grid-brick").each(function() {
                this.style.position = "";
                this.style.top = "";
                this.style.left = ""
            });
            var h = this.element[0].style;
            for (var i in this.originalStyle) {
                h[i] = this.originalStyle[i]
            }
            this.element.unbind(".grid").removeClass("grid").removeData("grid");
            f(e).unbind(".grid")
        }};
    /*!
     * jQuery imagesLoaded plugin v1.1.0
     * http://github.com/desandro/imagesloaded
     *
     * MIT License. by Paul Irish et al.
     */
    ;
    f.fn.imagesLoaded = function(o) {
        var m = this, k = m.find("img").add(m.filter("img")), h = k.length, n = "#", j = [];
        function l() {
            o.call(m, k)
        }
        function i(q) {
            var p = q.target;
            if (p.src !== n && f.inArray(p, j) === -1) {
                j.push(p);
                if (--h <= 0) {
                    setTimeout(l);
                    k.unbind(".imagesLoaded", i)
                }
            }
        }
        if (!h) {
            l()
        }
        k.bind("load.imagesLoaded error.imagesLoaded", i).each(function() {
            var p = this.src;
            this.src = n;
            this.src = p
        });
        return m
    };
    var c = function(h) {
        if (e.console) {
            e.console.error(h)
        }
    };
    f.fn.grid = function(j) {
        var h = function(z) {
            var H = f.extend({}, f.fn.grid.defaults, j);
            f.fn.grid.defaults.lazyLoad = j.lazyLoad;
            if (j == g) {
                j = {}
            }
            j.isFitWidth = H.isFitWidth;
            j.isAnimated = H.isAnimated;
            j.itemSelector = ".gbox";
            j.gutterWidth = H.horizontalSpaceBetweenThumbnails;
            var u = f(z).addClass("centered").addClass("grid-clearfix");
            var M = H.columnWidth;
            if (M == "auto") {
                j.columnWidth = function(ax) {
                    var ay = -999;
                    for (var aw = H.columns; aw >= 1; aw--) {
                        if (ay < H.columnMinWidth) {
                            ay = (((ax - (aw - 1) * j.gutterWidth) / aw) | 0)
                        }
                    }
                    u.find("div.gbox").width(ay);
                    return ay
                }
            } else {
                if ((typeof M) != "function") {
                    j.columnWidth = function(aw) {
                        var ax = M;
                        u.find("div.gbox").width(ax);
                        return ax
                    }
                }
            }
            u.find("div.gbox").css("margin-bottom", H.verticalSpaceBetweenThumbnails);
            var ad = (function() {
                var ay = document.createElement("div"), ax = "Khtml ms O Moz Webkit".split(" "), aw = ax.length;
                return function(az) {
                    if (az in ay.style) {
                        return true
                    }
                    az = az.replace(/^[a-z]/, function(aA) {
                        return aA.toUpperCase()
                    });
                    while (aw--) {
                        if (ax[aw] + az in ay.style) {
                            return true
                        }
                    }
                    return false
                }
            })();
            if (!ad("transform")) {
                u.addClass("noSupportTransform")
            } else {
                u.addClass("visible")
            }
            var P = null;
            var aq = u.data("directory");
            var ap = f("<div />").insertAfter(u);
            var L = function() {
                ap.addClass("grid-loader").removeClass("grid-loadMore").html("")
            };
            var V = function() {
                ap.removeClass("grid-loader")
            };
            var R = false;
            var r = function() {
                var ax = 0;
                for (var ay in P) {
                    var aw = P[ay];
                    ax++
                }
                if (ax > 0) {
                    R = false;
                    return true
                } else {
                    return false
                }
            };
            var w = function(aw) {
                if (aw) {
                    ap.addClass("grid-loadMore").html("LOAD MORE")
                } else {
                    ap.removeClass("grid-loadMore").html("")
                }
            };
            var D = aq;
            var v = D;
            var p = location.hash.substr(1);
            if (p != "" && H.hashTag && p != "*") {
                D += "/" + p;
                aq += "/" + p
            }
            var x = function(aw) {
                if (aw.substr(1, 1) == "-") {
                    return aw.substr(2)
                } else {
                    return aw
                }
            };
            var G = function(aA, ax) {
                var az = "";
                if (ax == "yes") {
                    az = "thumbnails/"
                }
                var aD = aA.split(/\.(?=[^.]*$)/)[0];
                var ay = "";
                if (aD.indexOf("$$") != -1) {
                    var aw = aD.split("$$");
                    aD = aw[0];
                    var aC = aw[1].split(":").join("/");
                    aC = aC.split("|").join("/");
                    ay = 'data-url="' + aC + '"'
                }
                aD = x(aD);
                var aB = '<div class="gbox" ' + ay + '><img src="' + D + "/" + az + aA + '" data-lightbox="' + D + "/" + aA + '" /><div class="image-caption"><h4>' + aD + '</h4></div><div class="lightbox-text">' + aD + "</div></div>";
                return aB
            };
            var al = function(ax, aE, aA) {
                var aD = "";
                if (aA.thumb == "yes") {
                    aD = "thumbnails/"
                }
                var az = x(ax);
                var ay = '<i class="icon-folder-open icon-white" style="margin-right:5px;"></i>' + aA.numFolders;
                var aC = '<i class="icon-picture icon-white" style="margin:0 5px 0 5px;"></i>' + aA.numImages;
                if (!H.showNumFolder) {
                    ay = ""
                }
                if (!H.showNumImages) {
                    aC = ""
                }
                if (H.autoHideNumFolder && aA.numFolders == 0) {
                    ay = ""
                }
                if (H.autoHideNumImages && aA.numImages == 0) {
                    aC = ""
                }
                var aw = "";
                if (aA.numImages == 0 && aE == "") {
                    aw = 'style="height:200px"'
                }
                var aB = '<div class="gbox gridFolder" data-folder="' + ax + '" ' + aw + '><img src="' + D + "/" + ax + "/" + aD + aE + '" /><div class="gradient-container"><div class="folder-info"><div class="folder-name">' + az + '</div><div class="folder-count">' + ay + aC + "</div></div></div></div>";
                return aB
            };
            var af = function(aw) {
                aw.each(function() {
                    var ax = f(this).removeClass("noTransform");
                    if (ax.hasClass("grid-brick")) {
                        if (ax.is(":hidden")) {
                            if (u.hasClass("noSupportTransform")) {
                                ax.show()
                            } else {
                                ax.show();
                                if (ax.data("moving") == false) {
                                    ax.animate(H.hiddenStyle, 0);
                                    ax.animate(H.visibleStyle, j.animOpts, function() {
                                        ax.addClass("noTransform")
                                    })
                                } else {
                                    ax.animate(H.visibleStyle, 0);
                                    ax.addClass("noTransform")
                                }
                            }
                        } else {
                            ax.animate(H.visibleStyle, j.animOpts, function() {
                                ax.addClass("noTransform")
                            })
                        }
                    }
                })
            };
            var k = function(aw) {
                aw = f(aw).addClass("loading-box");
                aw.css("margin-bottom", H.verticalSpaceBetweenThumbnails);
                u.append(aw.hide());
                u.imagesLoaded(function() {
                    aw.hide().css({top: 200, left: 200});
                    u.addClass("grid-with-loading-boxes");
                    u.grid("reload");
                    af(aw);
                    V();
                    w(r());
                    R = false
                })
            };
            var s = function(ax) {
                L();
                var aD = new Array();
                if (H.foldersAtTop) {
                    for (var aE in P) {
                        if (typeof P[aE] == "string") {
                        } else {
                            aD.push({image: aE, thumb: P[aE], folder: "yes"})
                        }
                    }
                    for (var aE in P) {
                        if (typeof P[aE] == "string") {
                            aD.push({image: aE, thumb: P[aE], folder: "no"})
                        } else {
                        }
                    }
                } else {
                    for (var aE in P) {
                        var aw = "yes";
                        if (typeof P[aE] == "string") {
                            aw = "no"
                        }
                        aD.push({image: aE, thumb: P[aE], folder: aw})
                    }
                }
                var aB = "";
                var aC = 0;
                while (aC < ax) {
                    if (aC >= aD.length) {
                        break
                    }
                    var aA = aD[aC];
                    var ay = aA.image.split(/\.(?=[^.]*$)/)[0];
                    if (ay != "folderCover") {
                        if (aA.folder == "yes") {
                            var az = aA.thumb.image;
                            aB += al(aA.image, az, aA.thumb)
                        } else {
                            aB += G(aA.image, aA.thumb)
                        }
                    }
                    delete P[aA.image];
                    aC++
                }
                k(aB)
            };
            var ao = function() {
                if (ap.hasClass("grid-loadMore")) {
                    s(H.imagesToLoad)
                }
            };
            ap.on("click", function() {
                ao()
            });
            if (H.lazyLoad) {
                u.waypoint(function(ax) {
                    var aw = f(this);
                    if (aw.hasClass("completeAddingImages")) {
                        aw.removeClass("completeAddingImages");
                        if (ap.hasClass("grid-loadMore")) {
                            ao()
                        }
                    }
                }, {context: e, continuous: true, enabled: true, horizontal: false, offset: "bottom-in-view", triggerOnce: false})
            }
            var y = f('<ul class="grid-breadcrumb grid-clearfix"></ul>');
            if (H.showNavBar) {
                y.insertBefore(u)
            }
            var n = function() {
                var ay = D.split("/");
                var ax = "";
                var aB = "";
                var aw = "";
                var az = "";
                for (var aA = 0; aA < ay.length; aA++) {
                    if (aB == "") {
                        aB += ay[aA]
                    } else {
                        aB += "/" + ay[aA]
                    }
                    var aC = x(ay[aA]);
                    if (aw == "") {
                        if (aB != v) {
                            continue
                        } else {
                            aw = "found it"
                        }
                    }
                    if (aB != v) {
                        az += "/" + ay[aA]
                    }
                    if (aA == ay.length - 1) {
                        ax += '<li data-directory="' + aB + '" class="active">' + aC + "</li>"
                    } else {
                        ax += '<li data-directory="' + aB + '"><a>' + aC + '</a><span class="divider">/</span></li>'
                    }
                }
                if (H.hashTag && az.substr(1) != "") {
                    location.hash = az.substr(1)
                } else {
                    if (H.hashTag && az.substr(1) == "") {
                        location.hash = "*"
                    }
                }
                y.html(ax)
            };
            n();
            var ar = function() {
                u.find(".gbox").remove().end().css("height", 0);
                ap.removeClass("grid-loadMore").html("");
                n();
                L();
                f.getJSON("reader.php?directory=" + D + "&albumsOrder=" + H.albumsOrder + "&imagesOrder=" + H.imagesOrder + "&folderCoverRandom=" + H.folderCoverRandom, function(aw) {
                    P = aw;
                    s(H.imagesToLoadStart)
                })
            };
            y.on("click", "a", function() {
                D = f(this).parent("li").data("directory");
                ar()
            });
            u.on("click", "div.gridFolder", function() {
                var aw = f(this).data("folder");
                D += "/" + aw;
                ar()
            });
            L();
            f.getJSON("reader.php?directory=" + aq + "&albumsOrder=" + H.albumsOrder + "&imagesOrder=" + H.imagesOrder + "&folderCoverRandom=" + H.folderCoverRandom, function(aw) {
                P = aw;
                s(H.imagesToLoadStart)
            });
            u.on("mouseenter.hoverdir, mouseleave.hoverdir", "div.gbox", function(aB) {
                if (!H.caption) {
                    return
                }
                var az = f(this), aA = aB.type, ay = az.find("div.image-caption"), aC = m(az, {x: aB.pageX, y: aB.pageY}), ax = aj(aC, az);
                var aD = ay.children("div.aligment");
                if (aD[0] == g) {
                    var aw = ay.html();
                    ay.html("<div class='aligment'><div class='aligment'>" + aw + "</div></div>")
                }
                if (aA === "mouseenter") {
                    if (H.captionType == "classic") {
                        ay.css({left: 0, top: 0});
                        ay.fadeIn(300);
                        return
                    }
                    ay.css({left: ax.from, top: ax.to});
                    ay.stop().show().fadeTo(0, 1, function() {
                        f(this).stop().animate({top: 0, left: 0}, 200, "linear")
                    })
                } else {
                    if (H.captionType == "classic") {
                        ay.css({left: 0, top: 0});
                        ay.fadeOut(300);
                        return
                    }
                    if (H.captionType == "grid-fade") {
                        ay.fadeOut(700)
                    } else {
                        ay.stop().animate({left: ax.from, top: ax.to}, 200, "linear", function() {
                            ay.hide()
                        })
                    }
                }
            });
            var m = function(ay, aB) {
                var ax = ay.width(), az = ay.height(), aw = (aB.x - ay.offset().left - (ax / 2)) * (ax > az ? (az / ax) : 1), aC = (aB.y - ay.offset().top - (az / 2)) * (az > ax ? (ax / az) : 1), aA = Math.round((((Math.atan2(aC, aw) * (180 / Math.PI)) + 180) / 90) + 3) % 4;
                return aA
            };
            var aj = function(ay, ax) {
                var az, aw;
                switch (ay) {
                    case 0:
                        if (!H.reverse) {
                            az = 0, aw = -ax.height()
                        } else {
                            az = 0, aw = -ax.height()
                        }
                        break;
                    case 1:
                        if (!H.reverse) {
                            az = ax.width(), aw = 0
                        } else {
                            az = -ax.width(), aw = 0
                        }
                        break;
                    case 2:
                        if (!H.reverse) {
                            az = 0, aw = ax.height()
                        } else {
                            az = 0, aw = -ax.height()
                        }
                        break;
                    case 3:
                        if (!H.reverse) {
                            az = -ax.width(), aw = 0
                        } else {
                            az = ax.width(), aw = 0
                        }
                        break
                }
                return{from: az, to: aw}
            };
            var Y = f("body");
            var ah = {interval: "none"};
            var q = 0;
            var S = f('<div class="autoGrid-lightbox" />').appendTo(Y);
            var X = f('<div class="autoGrid-nav" />').appendTo(S);
            var aa = f('<div class="autoGrid-close" />').appendTo(X);
            var W = f('<i class="iconClose" />').appendTo(aa);
            var B = f('<div class="autoGrid-play" />');
            if (H.lightboxPlayBtn) {
                B.appendTo(X)
            }
            var I = f('<i class="iconPlay" />').appendTo(B);
            var ae = f('<div class="autoGrid-lbcaption" />').appendTo(X).html("Here will go the text for the lightbox");
            var ak = f('<div class="autoGrid-next" />').appendTo(X);
            var au = f('<i class="iconNext" />').appendTo(ak);
            var F = f('<div class="autoGrid-prev" />').appendTo(X);
            var K = f('<i class="iconPrev" />').appendTo(F);
            var ac = f('<div class="lightbox-timer" />').appendTo(S);
            var t = aa.width();
            var Z = 3;
            if (H.lightboxPlayBtn) {
                Z = 4
            }
            var l = function() {
                var ay = S.outerWidth();
                if (ay < 650) {
                    ae.hide();
                    ak.css("width", (ay / Z));
                    F.css("width", (ay / Z));
                    B.css("width", (ay / Z));
                    aa.css("width", ay - ((ay / Z) * (Z - 1)))
                } else {
                    ae.show();
                    ak.css("width", t);
                    F.css("width", t);
                    B.css("width", t);
                    aa.css("width", t)
                }
                var aw = S.find("img");
                var ax = S.outerHeight() - X.outerHeight();
                aw.css("max-height", ax)
            };
            jQuery(e).resize(function() {
                l()
            });
            var U = new Image();
            var an = function() {
                U.onload = null;
                U = null;
                S.find(".lightbox-alignment").remove();
                S.find(".lightbox-alignment2").remove();
                S.find("img").remove()
            };
            var O = function() {
                S.find(".lb-loader").remove()
            };
            var E = function() {
                S.append('<div class="lb-loader"/>')
            };
            S.attr("unselectable", "on").css("user-select", "none").on("selectstart", false);
            var N = function() {
                ac.stop(true, true).width(0)
            };
            var at = function() {
                clearInterval(ah.interval)
            };
            var C = function() {
                if (H.lightBoxShowTimer == false) {
                    return
                }
                ac.css({position: "absolute", bottom: 0}).animate({width: "100%"}, H.lightBoxPlayInterval, "linear", function() {
                    N()
                })
            };
            var av = false;
            var ab = false;
            var Q = function() {
                ah.interval = setTimeout(function() {
                    o()
                }, H.lightBoxPlayInterval);
                C()
            };
            var A = function() {
                if (av && ab == false) {
                    N();
                    at();
                    Q()
                }
            };
            var ai = f("<span />");
            var J = function(aC, ax) {
                an();
                O();
                E();
                var aA = 0;
                var aB = 0;
                if (ax != true) {
                    aA = 0.9;
                    aB = H.lightBoxSpeedFx
                }
                if (H.lightBoxZoomAnim == false) {
                    aA = 1
                }
                var ay = aC;
                var az = ay.data("lightbox");
                if (az == g) {
                    az = ay.attr("src")
                }
                var aF = ay.siblings("div.lightbox-text").html();
                if (H.lightBoxText == false) {
                    aF = ""
                }
                var aE = "<div><div>" + aF + "</div></div>";
                ae.html(aE);
                U = new Image();
                var aw = f(U);
                var aD = U;
                U.onload = function() {
                    if (aD != U) {
                        return
                    }
                    O();
                    var aH = f('<div class="lightbox-alignment"></div>').appendTo(S);
                    var aG = f('<div class="lightbox-alignment2"></div>').appendTo(aH);
                    aG.append(aw.css("margin-top", -X.outerHeight()).hide().scale(aA));
                    aw.fadeIn(aB).animate({scale: "1"}, {duration: H.lightBoxSpeedFx, complete: function() {
                            A()
                        }});
                    l()
                };
                U.src = az;
                ai.stop(true);
                ai = f(U)
            };
            var am = false;
            u.on("click", "div.gbox", function() {
                am = true;
                var ay = f(this);
                if (ay.hasClass("gridFolder")) {
                    return
                }
                var ax = ay.data("url");
                if (ax != g) {
                    e.location.href = "http://" + ax;
                    return
                }
                if (H.lightBox == false) {
                    return
                }
                ab = false;
                q = u.find(".gbox").index(this);
                var aw = ay.children("img");
                X.animate({"margin-top": 0}, H.lightBoxSpeedFx);
                S.fadeIn(H.lightBoxSpeedFx);
                J(aw, true)
            });
            S.on("click", "div", function(aw) {
                aw.stopPropagation()
            });
            S.on("click", "img", function(aw) {
                aw.stopPropagation()
            });
            S.on("click", ".lightbox-alignment", function() {
                ag()
            });
            S.on("click", ".lightbox-alignment2", function() {
                ag()
            });
            S.on("click", function() {
                ag()
            });
            aa.on("click", function() {
                ag()
            });
            var ag = function() {
                if (H.lightBoxStopPlayOnClose) {
                    B.removeClass("selected");
                    av = false
                }
                am = false;
                ab = true;
                N();
                at();
                S.find(".lb-loader").remove();
                var ax = 0;
                if (H.lightBoxZoomAnim == false) {
                    ax = 1
                }
                var aw = S.find("img").stop().show();
                X.animate({"margin-top": -X.outerHeight()}, H.lightBoxSpeedFx);
                if (aw[0] != g) {
                    aw.animate({scale: ax}, H.lightBoxSpeedFx, function() {
                        S.fadeOut(100)
                    })
                } else {
                    S.fadeOut(100)
                }
            };
            var o = function() {
                ab = false;
                var az = u.find(".gbox");
                q += 1;
                if (q >= az.length) {
                    q = 0
                }
                if (!az.eq(q).is(":visible") || az.eq(q).hasClass("gridFolder")) {
                    var aw = q;
                    for (var ay = 0; ay < az.length; ay++) {
                        aw++;
                        if (aw >= az.length) {
                            aw = 0
                        }
                        var aA = az.eq(aw);
                        if (aA.is(":visible") && aA.hasClass("gridFolder") == false) {
                            q = aw;
                            break
                        }
                    }
                }
                var ax = az.eq(q).children("img");
                J(ax)
            };
            var T = function() {
                ab = false;
                var az = u.find(".gbox");
                q -= 1;
                if (q < 0) {
                    q = az.length - 1
                }
                if (!az.eq(q).is(":visible") || az.eq(q).hasClass("gridFolder")) {
                    var aw = q;
                    for (var ay = 0; ay < az.length; ay++) {
                        aw--;
                        if (aw < 0) {
                            aw = az.length - 1
                        }
                        var aA = az.eq(aw);
                        if (aA.is(":visible") && aA.hasClass("gridFolder") == false) {
                            q = aw;
                            break
                        }
                    }
                }
                var ax = az.eq(q).children("img");
                J(ax)
            };
            ak.on("click", function() {
                N();
                at();
                o()
            });
            S.on("click", "img", function() {
                N();
                at();
                o()
            });
            F.on("click", function() {
                N();
                at();
                T()
            });
            f(document).keyup(function(aw) {
                if (!H.lightboxKeyboardNav) {
                    return
                }
                if (aw.keyCode == "37") {
                    if (am == false) {
                        return
                    }
                    N();
                    at();
                    T()
                }
                if (aw.keyCode == "39") {
                    if (am == false) {
                        return
                    }
                    N();
                    at();
                    o()
                }
                if (aw.keyCode == 27) {
                    ag()
                }
            });
            if (H.swipeSupport) {
                S.on("dragstart", function(aw) {
                    aw.preventDefault()
                });
                S.touchSwipeLeft(function() {
                    N();
                    at();
                    o()
                }).touchSwipeRight(function() {
                    N();
                    at();
                    T()
                })
            }
            if (H.lightBoxAutoPlay) {
                B.addClass("selected");
                av = true
            }
            B.on("click", function() {
                z = f(this);
                if (z.hasClass("selected")) {
                    z.removeClass("selected");
                    av = false;
                    N();
                    at()
                } else {
                    z.addClass("selected");
                    av = true;
                    Q()
                }
            })
        };
        if (typeof j === "string") {
            var i = Array.prototype.slice.call(arguments, 1);
            this.each(function() {
                var k = f.data(this, "grid");
                if (!k) {
                    c("cannot call methods on grid prior to initialization; attempted to call method '" + j + "'");
                    return
                }
                if (!f.isFunction(k[j]) || j.charAt(0) === "_") {
                    c("no such method '" + j + "' for grid instance");
                    return
                }
                k[j].apply(k, i)
            })
        } else {
            this.each(function() {
                var k = f.data(this, "grid");
                if (k) {
                    k.option(j || {});
                    k._init()
                } else {
                    h(this);
                    f.data(this, "grid", new f.Mason(j, this))
                }
            })
        }
        return this
    };
    f.fn.grid.defaults = {imagesOrder: "byName", albumsOrder: "none", folderCoverRandom: true, foldersAtTop: true, showNumFolder: true, showNumImages: true, autoHideNumFolder: true, autoHideNumImages: false, isFitWidth: true, lazyLoad: true, showNavBar: true, imagesToLoadStart: 15, imagesToLoad: 5, horizontalSpaceBetweenThumbnails: 5, verticalSpaceBetweenThumbnails: 5, columnWidth: "auto", columns: 5, columnMinWidth: 195, isAnimated: true, caption: true, captionType: "grid", lightBox: true, lightboxKeyboardNav: true, lightBoxSpeedFx: 500, lightBoxZoomAnim: true, lightBoxText: true, lightboxPlayBtn: true, lightBoxAutoPlay: false, lightBoxPlayInterval: 4000, lightBoxShowTimer: true, lightBoxStopPlayOnClose: false, hashTag: false, swipeSupport: false, hiddenStyle: {opacity: 0, scale: 0.001}, visibleStyle: {opacity: 1, scale: 1}}
})(window, jQuery);



