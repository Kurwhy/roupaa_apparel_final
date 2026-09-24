tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                primary: "#f2ca50",
                "on-background": "#e2e2e2",
                background: "#131313",
                surface: "#1e1e1e",
                "surface-container-lowest": "#0e0e0e",
                "on-surface-variant": "#d0c5af",
                "outline-variant": "#4d4635",
                "on-primary": "#3c2f00",
                "surface-container-high": "#2a2a2a",
                "surface-container-low": "#1b1b1b",
            },
            animation: {
                "fade-in": "fadeIn 1s ease-out",
                "slide-up": "slideUp 0.8s ease-out forwards",
                "pulse-glow": "pulseGlow 2s infinite",
                float: "float 6s ease-in-out infinite",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                slideUp: {
                    "0%": { opacity: "0", transform: "translateY(20px)" },
                    "100%": { opacity: "1", transform: "translateY(0)" },
                },
                pulseGlow: {
                    "0%, 100%": {
                        boxShadow: "0 0 15px rgba(242, 202, 80, 0.2)",
                    },
                    "50%": { boxShadow: "0 0 25px rgba(242, 202, 80, 0.5)" },
                },
                float: {
                    "0%, 100%": { transform: "translateY(0)" },
                    "50%": { transform: "translateY(-10px)" },
                },
            },
        },
    },
};
