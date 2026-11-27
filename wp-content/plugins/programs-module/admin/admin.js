(function (wp) {
	'use strict';

	var el = wp.element.createElement;
	var useState = wp.element.useState;

	function SectionEditor( props ) {
		var row = props.row;
		var index = props.index;
		var update = props.update;

		function chooseImage() {
			var frame = wp.media({
				title: 'Select section image',
				button: { text: 'Use this image' },
				multiple: false,
			});
			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				update( index, 'image', attachment.id );
				update( index, 'imageUrl', attachment.url );
			} );
			frame.open();
		}

		return el( 'div', { className: 'upm-section', key: index },
			el( 'input', {
				placeholder: 'Section title',
				value: row.title || '',
				onChange: function ( event ) { update( index, 'title', event.target.value ); },
			} ),
			el( 'textarea', {
				placeholder: 'Description',
				value: row.description || '',
				onChange: function ( event ) { update( index, 'description', event.target.value ); },
			} ),
			el( 'div', { className: 'upm-image-control' },
				row.imageUrl ? el( 'img', { src: row.imageUrl, alt: '', className: 'upm-section-preview' } ) : null,
				el( 'button', { type: 'button', className: 'button', onClick: chooseImage }, row.image ? 'Change section image' : 'Select section image' ),
				row.image ? el( 'button', { type: 'button', className: 'button-link-delete', onClick: function () { update( index, 'image', 0 ); update( index, 'imageUrl', '' ); } }, 'Remove image' ) : null
			),
			el( 'button', { type: 'button', className: 'button-link-delete', onClick: function () { props.remove( index ); } }, 'Remove section' )
		);
	}

	function Editor() {
		var root = document.getElementById( 'upm-program-editor' );
		var initial = JSON.parse( root.dataset.sections || '[]' );
		var year = useState( root.dataset.year || '' );
		var duration = useState( root.dataset.duration || '' );
		var sections = useState( initial );

		function update( index, key, value ) {
			sections[1]( function ( currentSections ) { return currentSections.map( function ( row, rowIndex ) {
				if ( rowIndex !== index ) { return row; }
				var updated = Object.assign( {}, row );
				updated[ key ] = value;
				return updated;
			} ); } );
		}

		function remove( index ) {
			sections[1]( sections[0].filter( function ( row, rowIndex ) { return rowIndex !== index; } ) );
		}

		return el( 'div', { className: 'upm-editor' },
			el( 'p', {}, 'Use the fields below to describe this programme.' ),
			el( 'div', { className: 'upm-grid' },
				el( 'label', {}, 'Academic year', el( 'input', { name: 'upm_academic_year', value: year[0], onChange: function ( event ) { year[1]( event.target.value ); } } ) ),
				el( 'label', {}, 'Duration', el( 'input', { name: 'upm_duration', value: duration[0], onChange: function ( event ) { duration[1]( event.target.value ); } } ) )
			),
			el( 'input', { type: 'hidden', name: 'upm_sections', value: JSON.stringify( sections[0].map( function ( row ) { return { title: row.title || '', description: row.description || '', image: parseInt( row.image || 0, 10 ) }; } ) ) } ),
			el( 'div', { className: 'upm-sections' }, sections[0].map( function ( row, index ) { return el( SectionEditor, { row: row, index: index, update: update, remove: remove } ); } ) ),
			el( 'button', { type: 'button', className: 'button', onClick: function () { sections[1]( sections[0].concat( [ { title: '', description: '', image: 0, imageUrl: '' } ] ) ); } }, 'Add content section' )
		);
	}

	function mount() {
		var root = document.getElementById( 'upm-program-editor' );
		if ( root ) { wp.element.render( el( Editor ), root ); }
	}

	if ( 'loading' === document.readyState ) { document.addEventListener( 'DOMContentLoaded', mount ); } else { mount(); }
})( window.wp );
