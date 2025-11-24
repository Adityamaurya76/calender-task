<!-- Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="taskForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="taskModalLabel">Add Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" id="taskId" name="taskId" value="">
          <div class="mb-3">
              <label class="form-label">Title</label>
              <input type="text" name="title" id="title" class="form-control" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" id="description" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
              <label class="form-label">Due date</label>
              <input type="date" name="due_date" id="due_date" class="form-control" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Priority</label>
              <select name="priority" id="priority" class="form-select">
                  <option value="low">Low</option>
                  <option value="medium" selected>Medium</option>
                  <option value="high">High</option>
              </select>
          </div>
          <div class="mb-3">
              <label class="form-label">Category</label>
              <select name="category" id="category" class="form-select">
                  <option value="">-- Select --</option>
                  <option>Work</option>
                  <option>Personal</option>
                  <option>Shopping</option>
                  <option>Health</option>
                  <option>Education</option>
              </select>
          </div>
          <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status" id="status" class="form-select">
                  <option value="pending" selected>Pending</option>
                  <option value="completed">Completed</option>
              </select>
          </div>
          <div id="formErrors" class="text-danger"></div>
      </div>
      <div class="modal-footer">
        <button type="button" id="deleteTaskBtn" class="btn btn-danger me-auto" style="display:none;">Delete</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" id="saveTaskBtn" class="btn btn-primary">Save Task</button>
      </div>
    </form>
  </div>
</div>
