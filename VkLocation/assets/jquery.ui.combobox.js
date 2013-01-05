(function( $ ) {
    $.widget( "ui.combobox", {
        _create: function() {
            var options = this.options;

            var input,
                that = this,
            isOpen = false;

            var wrapper = this.wrapper = $( "<span>" )
                .insertBefore( this.element )

            var country_id = $('#' + $(this.element).attr('country_id'));
            var city_id = $('#' + $(this.element).attr('city_id'));
            var data_source = $(this.element).attr('data_source');

            country_id.change(function(){
                city_id.val("")
                input.val("")
            })

            input = $(this.element)
                .appendTo( wrapper )
                .autocomplete({
                    autoFocus: true,
                    delay: 0,
                    minLength: 0,
                    source: function( request, response)
                    {
                        $.ajax({
                            url: data_source,
                            dataType: "json",
                            data: {
                                query: request.term,
                                country: country_id.val(),
                                type: request.term ? "cities" : "large_cities"
                            },
                            success: function( data ) {

                                response( $.map( data, function( item ) {
                                    return {
                                        id: item.id,
                                        label: item.label,
                                        region: item.region || ""
                                    }
                                }));
                            }
                        });
                    },

                    change: function( event, ui ) {
                        if ( !ui.item )
                        {
                            return $(this).val("");
                        }
                    },
                    close: function (event, ui)
                    {
                        isOpen = false;
                    },
                    open: function(event, ui) {
                        isOpen = true;
                    },
                    select: function(event, ui) {
                        var item = ui.item;
                        city_id.val(item.id);
                        isOpen = false;
                        eval(options.onSelect);
                    }
                })
                .addClass( "input-manually" );

            input.data( "autocomplete" )._renderItem = function( ul, item ) {

                return $( "<li>" )
                    .data( "item.autocomplete", item )
                    .append( "<a>" + item.label + "</a>" )
                    .append("<small>"+item.region+"</small><br/>")
                    .appendTo( ul );
            };

            $( "<a>" )
                .attr( "tabIndex", -1 )
                .appendTo( wrapper )
                .button({
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false
                })
                .removeClass( "ui-corner-all" )
                .addClass( "ui-corner-right" ) //ui-combobox-toggle
                .click(function() {
                    // close if already visible
                    if (isOpen) {
                        input.autocomplete( "close" );
                        input.val("");
                        isOpen = false;
                        return;
                    }

                    isOpen = true;
                    // work around a bug (likely same cause as #5265)
                    $( this ).blur();

                    // pass empty string as value to search for, displaying all results
                    input.autocomplete( "search", "");
                    input.focus();
                });
        },

        destroy: function() {
            this.wrapper.remove();
            this.element.show();
            $.Widget.prototype.destroy.call( this );
        }
    });
})( jQuery );
