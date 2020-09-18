<?php


?>
<!DOCTYPE html>
<meta charset="utf-8">
<style>
    body {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        width: 960px;
        height: 500px;
        position: relative;
    }

    svg {
        width: 100%;
        height: 100%;
    }

    path.slice {
        stroke-width: 2px;
    }

    polyline {
        opacity: .3;
        stroke: black;
        stroke-width: 2px;
        fill: none;
    }
</style>

<body>

    <div id="test" style='margin:50px;width:960px;height:450px;background-color:#f3f3f3;'></div>
    <script src="https://d3js.org/d3.v3.min.js"></script>
    <script>
       // new pieChartLabels("body");
        function pieChartLabels(id, width, height) {
            var _pie = this;
            var svg = d3.select(id)
                .append("svg")
                .append("g")

            svg.append("g")
                .attr("class", "slices");
            svg.append("g")
                .attr("class", "labels");
            svg.append("g")
                .attr("class", "lines");

            /*var width = 960,
                height = 450,
                radius = Math.min(width, height) / 2;*/
            var radius =   Math.min(width, height) / 2;  
            var duration = 2000;
            var pie = d3.layout.pie()
                .sort(null)
                .value(function(d) {
                    return d.value;
                });

            var arc = d3.svg.arc()
                .outerRadius(radius * 0.8)
                .innerRadius(radius * 0.4);

            var outerArc = d3.svg.arc()
                .innerRadius(radius * 0.9)
                .outerRadius(radius * 0.9);

            svg.attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

            var key = function(d) {
                return d.data.label;
            };

            var colorCode = ["#98abc5", "#8a89a6", "#7b6888", "#6b486b", "#a05d56", "#d0743c", "#ff8c00"];
            

            this.shuffle = function(array) {
                var currentIndex = array.length,
                    temporaryValue, randomIndex;

                // While there remain elements to shuffle...
                while (0 !== currentIndex) {

                    // Pick a remaining element...
                    randomIndex = Math.floor(Math.random() * currentIndex);
                    currentIndex -= 1;

                    // And swap it with the current element.
                    temporaryValue = array[currentIndex];
                    array[currentIndex] = array[randomIndex];
                    array[randomIndex] = temporaryValue;
                }
                return array;
            };

            this.mergeWithFirstEqualZero = function(first, second) {
                var secondSet = d3.set();
                second.forEach(function(d) {
                    secondSet.add(d.label);
                });

                var onlyFirst = first
                    .filter(function(d) {
                        return !secondSet.has(d.label)
                    })
                    .map(function(d) {
                        return {
                            label: d.label,
                            value: 0
                        };
                    });
                return d3.merge([second, onlyFirst])
                    .sort(function(a, b) {
                        return d3.ascending(a.label, b.label);
                    });
            };

            this.create = function(data) {
                console.log("data", data);
                //var duration = +document.getElementById("duration").value;
                var color = d3.scale.category20().range(_pie.shuffle(colorCode));
                var data0 = svg.select(".slices").selectAll("path.slice")
                    .data().map(function(d) {
                        return d.data
                    });
                if (data0.length == 0) data0 = data;
                var was = _pie.mergeWithFirstEqualZero(data, data0);
                var is = _pie.mergeWithFirstEqualZero(data0, data);

                /* ------- SLICE ARCS -------*/

                var slice = svg.select(".slices").selectAll("path.slice")
                    .data(pie(was), key);

                slice.enter()
                    .insert("path")
                    .attr("class", "slice")
                    .style("fill", function(d) {
                        console.log("color ", d.data.label);
                        return color(d.data.label);
                    })
                    .each(function(d) {
                        this._current = d;
                    });

                slice = svg.select(".slices").selectAll("path.slice")
                    .data(pie(is), key);

                slice
                    .transition().duration(duration)
                    .attrTween("d", function(d) {
                        var interpolate = d3.interpolate(this._current, d);
                        var _this = this;
                        return function(t) {
                            _this._current = interpolate(t);
                            return arc(_this._current);
                        };
                    });

                slice = svg.select(".slices").selectAll("path.slice")
                    .data(pie(data), key);

                slice
                    .exit().transition().delay(duration).duration(0)
                    .remove();

                /* ------- TEXT LABELS -------*/

                var text = svg.select(".labels").selectAll("text")
                    .data(pie(was), key);

                text.enter()
                    .append("text")
                    .attr("dy", ".35em")
                    .style("opacity", 0)
                    .text(function(d) {
                        return d.data.label;
                    })
                    .each(function(d) {
                        this._current = d;
                    });

                function midAngle(d) {
                    return d.startAngle + (d.endAngle - d.startAngle) / 2;
                }

                text = svg.select(".labels").selectAll("text")
                    .data(pie(is), key);

                text.transition().duration(duration)
                    .style("opacity", function(d) {
                        return d.data.value == 0 ? 0 : 1;
                    })
                    .attrTween("transform", function(d) {
                        var interpolate = d3.interpolate(this._current, d);
                        var _this = this;
                        return function(t) {
                            var d2 = interpolate(t);
                            _this._current = d2;
                            var pos = outerArc.centroid(d2);
                            pos[0] = radius * (midAngle(d2) < Math.PI ? 1 : -1);
                            return "translate(" + pos + ")";
                        };
                    })
                    .styleTween("text-anchor", function(d) {
                        var interpolate = d3.interpolate(this._current, d);
                        return function(t) {
                            var d2 = interpolate(t);
                            return midAngle(d2) < Math.PI ? "start" : "end";
                        };
                    });

                text = svg.select(".labels").selectAll("text")
                    .data(pie(data), key);

                text
                    .exit().transition().delay(duration)
                    .remove();

                /* ------- SLICE TO TEXT POLYLINES -------*/

                var polyline = svg.select(".lines").selectAll("polyline")
                    .data(pie(was), key);

                polyline.enter()
                    .append("polyline")
                    .style("opacity", 0)
                    .each(function(d) {
                        this._current = d;
                    });

                polyline = svg.select(".lines").selectAll("polyline")
                    .data(pie(is), key);

                polyline.transition().duration(duration)
                    .style("opacity", function(d) {
                        return d.data.value == 0 ? 0 : .5;
                    })
                    .attrTween("points", function(d) {
                        this._current = this._current;
                        var interpolate = d3.interpolate(this._current, d);
                        var _this = this;
                        return function(t) {
                            var d2 = interpolate(t);
                            _this._current = d2;
                            var pos = outerArc.centroid(d2);
                            pos[0] = radius * 0.95 * (midAngle(d2) < Math.PI ? 1 : -1);
                            return [arc.centroid(d2), outerArc.centroid(d2), pos];
                        };
                    });

                polyline = svg.select(".lines").selectAll("polyline")
                    .data(pie(data), key);

                polyline
                    .exit().transition().delay(duration)
                    .remove();
            };
        }

        var lbl1 = {
            label: "Submitted : 1000",
            value: "1000"
        };
        var lbl2 = {
            label: "Shortlisted : 500",
            value: "500"
        };
        var lbl3 = {
            label: "Document Verified : 100",
            value: "100"
        };
        var lbl4 = {
            label: "Call For Interview: 100",
            value: "100"
        };
        var lbl5 = {
            label: "Invoice Generated: 300",
            value: "300"
        };
        var lbl6 = {
            label: "Fee Paid :400",
            value: "400"
        };
        var lbl7 = {
            label: "Admission Accepted: 500",
            value: "500"
        };
        var st = new Array();
        st.push(lbl1);
        st.push(lbl2);
        st.push(lbl3);
        st.push(lbl4);
        st.push(lbl5);
        st.push(lbl6);
        st.push(lbl7);

        var pie = new pieChartLabels("#test",960, 400);
        pie.create(st);

        //change(st);
        //change(randomData());
    </script>
</body>