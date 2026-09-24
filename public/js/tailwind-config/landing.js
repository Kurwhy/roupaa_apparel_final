tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                primary: "#f2ca50",
                "on-background": "#e2e2e2",
                background: "#131313",
                surface: "#131313",
                "surface-container-lowest": "#0e0e0e",
                "surface-container-low": "#1b1b1b",
                "surface-container-high": "#2a2a2a",
                "on-surface-variant": "#d0c5af",
                "outline-variant": "#4d4635",
                "on-primary": "#3c2f00",
                "primary-container": "#d4af37",
                outline: "#99907c",
            },
            fontFamily: {
                headline: ["Space Grotesk", "sans-serif"],
                body: ["Inter", "sans-serif"],
                label: ["Inter", "sans-serif"],
            },
            animation: {
                float: "float 6s ease-in-out infinite",
            },
            keyframes: {
                float: {
                    "0%, 100%": { transform: "translateY(0)" },
                    "50%": { transform: "translateY(-10px)" },
                },
            },
        },
    },
};
