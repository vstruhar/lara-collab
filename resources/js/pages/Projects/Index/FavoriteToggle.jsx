import { UnstyledButton, rem } from "@mantine/core";
import { IconStar, IconStarFilled } from "@tabler/icons-react";
import { useForm } from "laravel-precognition-react-inertia";
import classes from "./css/FavoriteToggle.module.css";

export default function ToggleFavorite({ item }) {
  const favorite = useForm("put", route("projects.favorite.toggle", item.id));

  return (
    <UnstyledButton
      onClick={() => favorite.submit({ preserveScroll: true })}
      className={classes.button}
      data-ignore-link
    >
      {item.favorite ? (
        <IconStarFilled
          style={{
            color: "var(--mantine-color-yellow-4)",
            width: rem(20),
            height: rem(20),
          }}
          data-ignore-link
        />
      ) : (
        <IconStar
          style={{
            width: rem(20),
            height: rem(20),
          }}
          data-ignore-link
        />
      )}
    </UnstyledButton>
  );
}
