import preset from "./vendor/filament/support/tailwind.config.preset";
import colors from "tailwindcss/colors";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";
import aspect from "@tailwindcss/aspect-ratio";

export default {
  presets: [preset],
  content: [
    "./app/Filament/**/*.php",
    "./resources/views/**/*.blade.php",
    "./vendor/filament/**/*.blade.php",
  ],
  safelist: [
    {
      pattern: /max-w-(sm|md|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl)/,
      variants: ["sm", "md", "lg", "xl", "2xl"],
    },
  ],
  theme: {
    extend: {
      colors: {
        danger: colors.rose,
        primary: colors.blue,
        success: colors.green,
        warning: colors.yellow,
        gray: colors.gray,
        orange: colors.orange,
        positive: colors.emerald,
        info: colors.sky,
      },

      inset: {
        "-0.5": "-0.125rem",
      },
      spacing: {
        44: "11rem",
        18: "4.5rem",
        95: "23.75rem",
        125: "31.25rem",
        140: "35rem",
      },
      opacity: {
        15: ".15",
        30: "0.3",
        40: "0.4",
      },
      minHeight: {
        "(screen-content)": "calc(100vh - 9.625rem)",
      },
    },
  },
  plugins: [forms, typography, aspect],
};
