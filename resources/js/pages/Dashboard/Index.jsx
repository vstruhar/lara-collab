import Layout from "@/layouts/MainLayout";
import { usePage } from "@inertiajs/react";
import { Title } from "@mantine/core";
import Masonry from "react-masonry-css";
import OverdueTasks from "./Cards/OverdueTasks";
import { ProjectCard } from "./Cards/ProjectCard";
import RecentComments from "./Cards/RecentComments";
import RecentlyAssignedTasks from "./Cards/RecentlyAssignedTasks";
import classes from "./css/Index.module.css";

const Dashboard = () => {
  const { projects, overdueTasks, recentlyAssignedTasks, recentComments } = usePage().props;

  const breakpointColumns = {
    default: 3,
    1100: 2,
    700: 1,
  };

  return (
    <>
      <Title mb="xl">Dashboard</Title>
      <Masonry
        breakpointCols={breakpointColumns}
        className={classes.myMasonryGrid}
        columnClassName={classes.myMasonryGridColumn}
      >
        {projects.map((project) => (
          <ProjectCard key={project.id} project={project} />
        ))}
        <OverdueTasks tasks={overdueTasks} />
        <RecentlyAssignedTasks tasks={recentlyAssignedTasks} />
        <RecentComments comments={recentComments} />
      </Masonry>
    </>
  );
};

Dashboard.layout = (page) => <Layout title="Dashboard">{page}</Layout>;

export default Dashboard;
