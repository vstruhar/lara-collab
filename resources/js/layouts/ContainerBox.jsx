import { Paper } from "@mantine/core";
import classes from "./css/ContainerBox.module.css";

export default function ContainerBox({ children, ...props }) {
  return (
    <Paper px={40} py={30} withBorder className={classes.box} {...props}>
      {children}
    </Paper>
  );
}
