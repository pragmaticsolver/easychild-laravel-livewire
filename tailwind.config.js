const defaultTheme = require("tailwindcss/defaultTheme")

module.exports = {
    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter var", ...defaultTheme.fontFamily.sans]
            },
            colors: {
                ...defaultTheme.colors,
                "pie-text": "#5850EC",
                "pie-red": {
                    bg: "#FEE6F0",
                    fg: "#FC0776"
                },
                "pie-yellow": {
                    bg: "#FDEBD3",
                    fg: "#FFA220"
                },
                "pie-orange": {
                    bg: "#FEF5E8",
                    fg: "#FA5E46"
                },
                calendar: {
                    light: "#8C87F9",
                    dark: "#5850EC"
                },
                report: {
                    mantis: "#86C474",
                    chelsea: "#7FAB4D",
                    oslo: "#7F888E"
                }
            },
            spacing: {
                "2.5": "0.625rem",
                "17": "4.5rem"
            },
            width: {
                "1/7": "14.2857143%"
            },
            placeholderColor: theme => theme("colors"),
            borderWidth: {
                ...defaultTheme.borderWidth,
                "3": "3px"
            },
            spacing: {
                vhscreen: "calc(var(--vh, 1vh) * 100)",
                pdfmodal: "calc(var(--vh, 1vh) * 100 - 65px)",
                "pdfmodel-sm": "calc(var(--vh, 1vh) * 100 - 80px)",
                "pdfmodel-md": "calc(var(--vh, 1vh) * 100 - 110px)",
                calendarscreen: "calc(var(--vh, 1vh) * 100 - 60px - 64px)",
                notificationdrop: "calc(var(--vh, 1vh) * 100 - 60px - 64px - 40px)"
            },
            maxHeight: {
                ...defaultTheme.spacing,
                mscreen: "calc(100vh - 30px)",
                nscreen: "calc(100vh - 64px - 15px)"
            },
            maxWidth: {
                ...defaultTheme.spacing
            },
            minWidth: {
                ...defaultTheme.spacing
            },
            height: {
                ...defaultTheme.spacing
            },
            minHeight: {
                ...defaultTheme.spacing,
                "17": "4.5rem"
            },
            fontSize: {
                xss: "0.625rem",
                "7xl": "5.5rem"
            },
            rotate: {
                "90": "90deg"
            }
        }
    },
    variants: {
        placeholderColor: ["responsive", "focus"],
        visibility: ["responsive", "group-hover"],
        backgroundColor: ["responsive", "hover", "focus", "disabled"],
        opacity: ["responsive", "hover", "focus", "disabled"],
        cursor: ["responsive", "disabled"],
        outline: ["responsive", "hover", "focus"]
    },
    purge: {
        content: [
            "./app/**/*.php",
            "./resources/**/*.html",
            "./resources/**/*.js",
            "./resources/**/*.jsx",
            "./resources/**/*.ts",
            "./resources/**/*.tsx",
            "./resources/**/*.php",
            "./resources/**/*.blade.php",
            "./resources/**/*.vue",
            "./resources/**/*.twig"
        ],
        options: {
            defaultExtractor: content => content.match(/[\w-/.:]+(?<!:)/g) || [],
            whitelistPatterns: [/-active$/, /-enter$/, /-leave-to$/, /show$/]
        }
    },
    future: {
        removeDeprecatedGapUtilities: true
    },
    plugins: [
        require("@tailwindcss/aspect-ratio"),
        require("@tailwindcss/custom-forms"),
        require("@tailwindcss/ui"),
        require("@tailwindcss/line-clamp")
    ]
}
