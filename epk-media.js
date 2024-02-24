jQuery(document).ready(($) => {
	const container = 'epk-media-form';
	const selected = 'epk-medias-container-selected';
	const unselected = 'epk-medias-container-unselected';
	const urlParams = new URLSearchParams(window.location.search);
	const tab = urlParams.get('tab') ?? '';
	const Tab = tab.charAt(0).toUpperCase() + tab.slice(1).toLowerCase();
	const plural = tab === 'audio' ? 'audio files' : `${tab}s`;

	let modified = false;

	function maybeFollowNavLink(e) {
		if (modified && tab) {
			// MIME type tab is selected
			e.preventDefault();
			if (
				$(e.target).hasClass('nav-tab-active') ||
				$(e.target).parent().hasClass('nav-tab-active')
			) {
				$('.nav-tab-active').blur();
				return;
			}

			const href =
				$(e.target).attr('href') ?? $(e.target).parent().attr('href');

			if (confirm('Are you sure you want to leave this tab?')) {
				window.location = href;
			}
		}
	}

	const moveMediaUp = (mover, movee) => {
		modified = true;
		$(`#${movee.attr('id')}`).before(mover);
	};

	const moveMediaDown = (mover, movee) => {
		modified = true;
		$(`#${movee.attr('id')}`).after(mover);
	};

	const reload = () => {
		location.reload();
	};

	const addUnselected = (src, title, order, rawID) => {
		$(`#${id}`).remove();
		id = id.replace('unselected-', '');
		modified = true;

		const img =
			tab === 'audio' || tab === 'video'
				? `/wp-includes/images/media/${tab}.png`
				: src;

		const newSelected = `<div class="epk-media-container" data-id=${id} data-order=${order} id="selected-${id}">
			<img src="${img}" alt="${title}" />
			<div class="epk-media-meta">
				<p>Title: ${title}</p>
				<p>URL: <a href="${src}" title="See Full ${Tab}">${src}</a></p>
				<p>Order: ${order}</p>
				<p>Edit: <a href="/wp-admin/upload.php?item=${id}" title="Edit This ${Tab}">${id}</a></p>
			</div>
			<div class="epk-media-controls">
				<div class="reorder-buttons">
					<button type="button" class="move-up" title="Move Up">&#8593;</button>
					<button type="button" class="move-down" title="Move Down">&#8595;</button>
				</div>
				<div class="toggle-render checkbox-wrapper">
					<input type="checkbox" class="checkbox" checked>
				</div>
			</div>
		</div>`;

		$(`.${selected}`).append(newSelected);
	};

	const getLowestMenuOrder = () => {
		let lowest = Infinity;

		$(`.${selected} .epk-media-container`).each(function () {
			const order = $(this).data('order');
			if (order < lowest) {
				lowest = order;
			}
		});

		return lowest;
	};

	const reset = () => {
		if (confirm('Are you sure you want to lose your changes?')) {
			// confirm & page refresh
			reload();
		}
	};

	const submit = (e) => {
		if (!confirm('Save your changes?')) {
			e.preventDefault();
		} else {
			const getLowest = getLowestMenuOrder();
			const lowest = getLowest < 1 ? 1 : getLowest;
			const medias = [];

			let order = lowest;

			$(`.${selected}`)
				.find('.epk-media-container')
				.each(function () {
					const id = $(this).attr('id').replace('selected-', '');
					const render = $(this)
						.find('.toggle-render input[type="checkbox"]')
						.prop('checked');

					const media = {
						id: id,
						order: order,
						render: render,
					};

					medias.push(media);
					order++;
				});

			$('#epk-media').val(JSON.stringify(medias));
		}
	};

	const handleClick = (action, e) => {
		action === 'reset' ? reset() : submit(e);
	};

	$('.nav-tab-wrapper').on('click', '.nav-tab', (e) => {
		maybeFollowNavLink(e);
	});

	$(`.${selected}`).on('click', '.move-up', function () {
		const mover = $(this).closest('.epk-media-container');
		const movee = mover.prev('.epk-media-container');
		moveMediaUp(mover, movee);
	});

	$(`.${selected}`).on('click', '.move-down', function () {
		const mover = $(this).closest('.epk-media-container');
		const movee = mover.next('.epk-media-container');
		moveMediaDown(mover, movee);
	});

	$(`.${selected}`).on('click', '.checkbox', () => {
		modified = true;
	});

	$(`.${container} .buttons`).on('click', 'button', function (e) {
		const action = $(this).attr('title').toLowerCase();
		handleClick(action, e);
	});

	$(`.${unselected} .epk-media-single`).on('click', function () {
		const title = $(this).attr('title').replace('Add ', '');
		const src = $(this).data('src');
		const order = $(this).data('order') ?? 0;
		const id = $(this).attr('id');

		if ($('.instructions').hasClass('no-media')) {
			$(`.${selected} .instructions`)
				.removeClass('no-media')
				.text(
					`The ${plural} in this box will be rendered on the homepage in this order.`
				);
		}

		addUnselected(src, title, order, id);

		$(this).remove();

		if (!$('.epk-media-grid').children().length) {
			$(`.${unselected}`).remove();
			$(`.${selected} .instructions`).remove();
		}
	});
});
