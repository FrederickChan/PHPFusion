
(function($) {

    let selBtn = $('.btn-theme-options');

    $.fn.colorSchemeManager = function() {

        function getSystemScheme() {
            let systemScheme = 'light';
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                systemScheme = 'dark';
            }
            return systemScheme;
        }

        function getPreferredColorScheme() {
            let systemScheme = getSystemScheme(), chosenScheme = systemScheme;

            if (localStorage.getItem("scheme")) {
                chosenScheme = localStorage.getItem("scheme");
            }

            if (systemScheme === chosenScheme) {
                localStorage.removeItem("scheme");
            }

            return chosenScheme;
        }

        function savePreferredColorScheme(scheme) {
            let systemScheme = 'light';
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                systemScheme = 'dark';
            }
            if (systemScheme === scheme) {
                localStorage.removeItem("scheme");
            } else {
                localStorage.setItem("scheme", scheme);
            }
        }

        function toggleColorScheme(scheme) {
            localStorage.setItem('selectedScheme', scheme);

            let newScheme = "light";
            if (scheme === 'auto') {
                newScheme = getSystemScheme();
            }
            if (scheme === "dark") {
                newScheme = "dark";
            }

            $('.btn-theme-options').removeClass('active');
            $('.btn-theme-options[data-bs-theme-value=' + scheme + ']').addClass('active');

            applyPreferredColorScheme(newScheme);
            savePreferredColorScheme(newScheme);
        }

        function applyPreferredColorScheme(scheme) {

            $('html').attr('data-luna-theme', scheme); //attr('data-color-scheme', scheme);

            // for (var s = 0; s < document.styleSheets.length; s++) {
            //     try {
            //         for (var i = 0; i < document.styleSheets[s].cssRules.length; i++) {
            //             rule = document.styleSheets[s].cssRules[i];
            //             if (rule && rule.media && rule.media.mediaText.includes("prefers-color-scheme")) {
            //                 switch (scheme) {
            //                     case "light":
            //                         rule.media.appendMedium("original-prefers-color-scheme");
            //                         if (rule.media.mediaText.includes("light")) rule.media.deleteMedium("(prefers-color-scheme: light)");
            //                         if (rule.media.mediaText.includes("dark")) rule.media.deleteMedium("(prefers-color-scheme: dark)");
            //                         break;
            //                     case "dark":
            //                         rule.media.appendMedium("(prefers-color-scheme: light)");
            //                         rule.media.appendMedium("(prefers-color-scheme: dark)");
            //                         if (rule.media.mediaText.includes("original")) rule.media.deleteMedium("original-prefers-color-scheme");
            //                         break;
            //                     default:
            //                         rule.media.appendMedium("(prefers-color-scheme: dark)");
            //                         if (rule.media.mediaText.includes("light")) rule.media.deleteMedium("(prefers-color-scheme: light)");
            //                         if (rule.media.mediaText.includes("original")) rule.media.deleteMedium("original-prefers-color-scheme");
            //                         break;
            //                 }
            //             }
            //         }
            //     } catch (error) {
            //         //console.error(`Error: ${error.message}`);
            //     }
            // }

            let currentToggle = scheme;
            if (localStorage.getItem("selectedScheme")) {
                currentToggle = localStorage.getItem('selectedScheme');
            }
            $('.btn-theme-options').removeClass('active');
            $('.btn-theme-options[data-bs-theme-value=' + currentToggle + ']').addClass('active');
        }


        // Attach click event handler to .btn-theme-options
        selBtn.on('click', function(e) {
            e.preventDefault();
            let val = $(this).data('bs-theme-value');
            toggleColorScheme(val);
        });

        applyPreferredColorScheme(getPreferredColorScheme());
    };
}(jQuery));
