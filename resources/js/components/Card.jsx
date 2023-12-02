import { Card as MantineCard } from "@mantine/core";
import classes from "./css/Card.module.css";

export default function Card({ children, ...props }) {
  return (
    <MantineCard withBorder radius="md" padding="lg" className={classes.card} {...props}>
      {children}
    </MantineCard>
  );
}
