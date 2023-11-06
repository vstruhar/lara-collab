import { UnstyledButton, rem } from "@mantine/core";
import { IconStar, IconStarFilled } from "@tabler/icons-react";
import { useForm } from "laravel-precognition-react-inertia";

export default function ToggleFavorite({ item }) {
  const favorite = useForm("put", route("projects.favorite.toggle", item.id));

  const toggleFavorite = () => favorite.submit();

  return (
    <UnstyledButton onClick={toggleFavorite}>
      {item.favorite ? (
        <IconStarFilled
          style={{
            color: "var(--mantine-color-yellow-4)",
            width: rem(20),
            height: rem(20),
          }}
        />
      ) : (
        <IconStar
          style={{
            width: rem(20),
            height: rem(20),
          }}
        />
      )}
    </UnstyledButton>
  );
}
