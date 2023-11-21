import axios from 'axios';
import { produce } from "immer";

const createTaskCommentsSlice = (set, get) => ({
  comments: [],
  fetchComments: async (task, onFinish) => {
    try {
      const { data } = await axios.get(route("projects.tasks.comments", [task.project_id, task.id]));
      onFinish();

      return set(produce(state => {state.comments = data}));
    } catch (e) {
      onFinish();
      console.error(e);
      alert("Failed to load comments");
    }
  },
  saveComment: async (task, comment, onFinish) => {
    try {
      const { data } = await axios.post(
        route("projects.tasks.comments.store", [task.project_id, task.id]),
        { content: comment },
        { progress: true }
      );
      onFinish();

      return set(produce(state => {
        state.comments = [
          data.comment,
          ...state.comments,
        ];
      }));
    } catch (e) {
      onFinish();
      console.error(e);
      alert("Failed to save comment");
    }
  },
});

export default createTaskCommentsSlice;
