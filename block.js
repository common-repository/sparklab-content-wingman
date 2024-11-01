/**
 * SparkLab: Block one
 *
 * Adding functionality for generating AI based content
 */
( function ( blocks, editor, i18n, element, blockEditor ) {
	let el = element.createElement;
	let __ = i18n.__;
	let RichText = editor.RichText;
	let AlignmentToolbar = editor.AlignmentToolbar;
	let BlockControls = editor.BlockControls;
	let useBlockProps = blockEditor.useBlockProps;
	const TextControl = wp.components.TextControl;
	const SelectControl = wp.components.SelectControl;
	const TextAreaControl = wp.components.TextareaControl;
	const PanelBody = wp.components.PanelBody;
	let InspectorControls = wp.blockEditor.InspectorControls;

	blocks.registerBlockType( 'sparklab-content-wingman/block-controls', {
		title: __( 'SparkLab: Content Wingman', 'sparklab-content-wingman-title' ),
		icon: 'universal-access-alt',
		category: 'layout',

		attributes: {
			content: {
				type: 'array',
				source: 'children',
				selector: 'p',
			},
			alignment: {
				type: 'string',
				default: 'none',
			},
		},
		edit: function ( props ) {
			let content = props.attributes.content;
			let alignment = props.attributes.alignment;
			let attributes = props.attributes;
			const $ = jQuery;

			function onChangeContent( newContent ) {
				props.setAttributes( { content: newContent } );
			}

			function onChangeAlignment( newAlignment ) {
				props.setAttributes( {
					alignment:
						newAlignment === undefined ? 'none' : newAlignment,
				} );
			}

			return [
				el(
					InspectorControls,
					{
						key: "inspector"
					},
					React.createElement(
						PanelBody,
						{ title: __(' ') },
						React.createElement(
							'div',
							{
								"class": "sparklab-loader hidden"
							},
							React.createElement(
								'div',
								useBlockProps( { class: "sparklab-loader-paragraph"} ),
								__('Loading...')
							)
						),
						React.createElement(
							'div',
							{
								'id': 'sparklab-form',
								'class' : 'sparkform'
							},
							React.createElement(
								SelectControl,
								{
									"label": __('Content Type'),
									"placeholder": "Enter your topic here",
									"class": "pcomponents-text-control__input",
									"id" : "sparklab-form-input-content-type",
									options: [
										{ value: 'blog_intro', label: 'Blog Intro' },
										{ value: 'paragraph', label: 'Paragraph' },
										{ value: 'product_description', label: 'Product Description' },
									],
									onChange: function (e) {
										let content_type = $('#sparklab-form-input-content-type').val();
										if(content_type == 'blog_intro' || content_type == 'paragraph') {
											$('#sparklab-form-input-title').attr('placeholder', 'Your Topic')
											$('#sparklab-form-input-description').addClass('hide');
										}else if (content_type == 'product_description'){
											$('#sparklab-form-input-title').attr('placeholder', 'Product Name')
											$('#sparklab-form-input-description').removeClass('hide');
										}
									}
								}
							)
						), //Input End
						//Input Start
						React.createElement(
							'div',
							{
								'id': 'sparklab-form',
								'class' : 'sparkform'
							},

							React.createElement(
								TextControl,
								{
									"label": __(''),
									"placeholder": "Your Topic",
									"class": "pcomponents-text-control__input",
									"id" : "sparklab-form-input-title",
									"value" : attributes.title,
									onChange: function (e) {

									}
								}
							)
						), //Input End
						//Input Start
						React.createElement(
							'div',
							{
								'id': 'sparklab-form',
								'class' : 'sparkform'
							},

							React.createElement(
								TextAreaControl,
								{
									"label": __(''),
									"placeholder": "Short Description about your product",
									"class": "pcomponents-text-control__input hide",
									"id" : "sparklab-form-input-description",
									"value" : attributes.pDescription,
									onChange: function (e) {

									}
								}
							)
						),//Input End
						React.createElement(
							"p",
							{ "class": "", "id" : "" },
							React.createElement(
								"button",
								{
									"class": "button button-secondary",
									"id" : "sparklab-generate-button",
									onClick: function() {
										$('.pcomponents-text-control__input').removeClass("red-border");
										let title = $('#sparklab-form-input-title');
										let description = $('#sparklab-form-input-description');
										let content_type = $('#sparklab-form-input-content-type').val();
										let title_str = '';
										if(title.val() === '') {
											title.addClass("red-border");
										}
										if(content_type === 'paragraph') {
											title_str+='Write a paragraph about: '+title.val();
										}
										if(content_type === 'blog_intro') {
											title_str+='Write a blog intro about: '+title.val();
										}
										if(content_type === 'product_description') {
											title_str+='Write a product description for: '+title.val() + description.val();
										}
										if( title_str !=='' && title.val() !== '') {
											$('.sparklab-loader').removeClass("hidden");
											jQuery.ajax({
												type: "POST",
												dataType: "json",
												data: {'action': 'get_data', 'query_str': title_str},
												/* the hash here is important because we are required to do rate limiting by OpenAI */
												url: "admin-ajax.php",
												success: function(response){
													props.setAttributes({content: response.data });
													$('.sparklab-loader').addClass("hidden");
													console.log("on ajax call" + props.attributes.content);
												}}

											);
										}
									}
								},
								"Generate"
							)
						)
					)
				),
				el(
					RichText,
					useBlockProps( {
						key: 'richtext',
						tagName: 'p',
						style: { textAlign: alignment },
						className: props.className,
						placeholder: __( 'SparkLab: Content Wingman' ),
						onChange: onChangeContent,
						value: content,
					} )
				),
			];
		},

		save: function ( props ) {
			return el(
				RichText.Content,
				useBlockProps.save( {
					tagName: 'p',
					className:
						'sparklab-cwm-align-' +
						props.attributes.alignment,
					value: props.attributes.content,
				} )
			);
		},
	} );
} )(
	window.wp.blocks,
	window.wp.editor,
	window.wp.i18n,
	window.wp.element,
	window.wp.blockEditor
);
