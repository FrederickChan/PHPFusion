$(document).on('click', '.section-view', function (event) {
    event.preventDefault();
    let container = $(this).siblings('ul.menu-container'),
        caret = $(this).find('i');

    if (container.length) {
        if (container.is(':visible')) {
            container.slideUp();
            caret.removeClass('fa-angle-down').addClass('fa-angle-right');

        } else {
            container.slideDown();
            caret.removeClass('fa-angle-right').addClass('fa-angle-down');
        }
    }
});

// slider

$(document).on('click', 'button[data-toggle="sidex"]', function (e) {
    e.preventDefault();
    let side_body = $(this).closest('.pf-side').find('.pf-side-body'),
        span = $(this).children('span');
    if (side_body.is(':visible')) {
        span.text('Expand');
        side_body.slideUp();
    } else {
        span.text('Close');
        side_body.slideDown();
    }
});


let checkboxToggle = function (eInput, eDOM) {

    if (eInput.length) {
        $(document).on('change', '#' + eInput, function (e) {
            var inputDOM = $('#' + eInput);
            var elemDOM = $('#' + eDOM);

            if (inputDOM.is(':checked')) {
                elemDOM.slideDown();
            } else {
                elemDOM.slideUp();
            }

        });
    }

}

/* Fusion 404 Logo*/
let animate_fusionpro404 = function () {
    let select = s => document.querySelector(s), selectAll = s => document.querySelectorAll(s);

    gsap.set('svg', {
        visibility: 'visible'
    })

    let svgns = "http://www.w3.org/2000/svg";
    let container = select("#container");
    let twoPi = Math.PI * 2;

    for (let i = 0; i < 25; i++) {
        createCircle();
    }

    function createCircle() {

        var circle = document.createElementNS(svgns, "circle");
        container.appendChild(circle);

        var radius = Math.random() < 0.35 ? gsap.utils.random(-50, 40) : gsap.utils.random(-50, 50);


        gsap.set(circle, {
            attr: {r: gsap.utils.random(5, 12), cx: "50%", cy: 170},
            x: gsap.utils.random(-twoPi, twoPi),
            y: gsap.utils.random(-twoPi, twoPi)
        });

        let swarmTl = gsap.timeline();
        swarmTl.to(circle, {
            duration: gsap.utils.random(2, 6),
            x: "+=" + twoPi,
            repeat: -1,
            modifiers: {
                x: gsap.utils.unitize(x => (Math.cos(x) * radius), 'px')
            },
            ease: 'none'
        })
            .to(circle, {
                duration: 2,
                y: "+=" + twoPi,
                repeat: -1,
                modifiers: {
                    y: gsap.utils.unitize(y => (Math.sin(y) * radius), 'px')
                },
                ease: 'none'
            }, 0);
    }

    gsap.set('#reflection', {
        scaleY: -1,
        y: 210,
        opacity: 0.12
    })

    gsap.to('#gridBox, #ring', {
        duration: 0.061,
        opacity: 'random(0.64, 0.97)',
        ease: 'sine.inOut',
        repeatRefresh: true,
        repeat: -1
    })

    gsap.to('.gridBox', {
        attr: {
            y: gsap.utils.wrap(['+=40', '+=20', 0])
        },
        ease: 'sine.inOut',
        repeat: -1,
        yoyo: true,
        duration: 1.4,
    })

    gsap.to('#ring', {
        scale: 1.25,
        transformOrigin: '50% 50%',
        ease: 'sine.inOut',
        repeat: -1,
        yoyo: true,
        duration: 1.4,
    })

    gsap.globalTimeline.timeScale(0.75)
}

// Post button
